<?php

class ChgtDenomRegenerateLotsTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "Document id"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'chgtdenom';
        $this->name = 'regenerate-lots';
        $this->briefDescription = "Regénère les mouvements de facturation d'un document";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {

        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $renameLot = null;
        $chgt = ChgtDenomClient::getInstance()->find($arguments['doc_id']);

        if(count($chgt->origine_cepages)) {
            $volumteCepageTotal = 0;
            foreach($chgt->origine_cepages as $k => $v) {
                $volumteCepageTotal += $v;
            }

            if($volumteCepageTotal != $chgt->origine_volume && count($chgt->origine_cepages) == 1) {
                foreach($chgt->origine_cepages as $k => $v) {
                    $chgt->origine_cepages[$k] = $chgt->origine_volume;
                }
            }
        }

        if(!$chgt->changement_origine_id_document) {
            $chgt->changement_origine_lot_unique_id = null;
        }

        $lotOrigine = $chgt->getLotOrigine();

        if($lotOrigine) {
            $chgt->origine_millesime = $lotOrigine->millesime;
            $chgt->origine_volume = $lotOrigine->volume;
            $chgt->origine_specificite = $lotOrigine->specificite;
            $chgt->origine_produit_hash = $lotOrigine->produit_hash;
            $chgt->origine_cepages = $lotOrigine->cepages;
            $chgt->origine_produit_libelle = $lotOrigine->produit_libelle;
            $chgt->origine_numero_logement_operateur = $lotOrigine->numero_logement_operateur;
            $chgt->origine_affectable = $lotOrigine->affectable;
        }

        $chgt->origine_specificite = str_replace("UNDEFINED", "", $chgt->origine_specificite);
        if(!$chgt->origine_specificite) {
            $chgt->origine_specificite = null;
        }
        $chgt->changement_specificite = str_replace("UNDEFINED", "", $chgt->changement_specificite);
        if(!$chgt->changement_specificite) {
            $chgt->changement_specificite = null;
        }

        $lots = $chgt->lots->toJson();

        if(count($lots) == 1) {
            $lots[1] = $lots[0];
            $lots[0] = null;
        }
        $chgt->campagne = null;
        $chgt->getCampagne();
        $chgt->generateLots();

        $documentOrdresToRewrite = array();

        if($lots[0]) {
            $chgt->lots[0]->document_ordre = $lots[0]->document_ordre;
            $chgt->lots[0]->campagne = $lots[0]->campagne;
            $chgt->lots[0]->numero_archive = $lots[0]->numero_archive;
            $chgt->lots[0]->numero_dossier = $lots[0]->numero_dossier;
            $chgt->lots[0]->unique_id = $lots[0]->unique_id;
            $chgt->lots[0]->id_document_provenance = $lots[0]->id_document_provenance;
            $chgt->lots[0]->id_document_affectation = $lots[0]->id_document_affectation;
            $chgt->lots[0]->affectable = $lots[0]->affectable;
        } else {
            $documentOrdresToRewrite[] = $chgt->lots[0]->unique_id;
        }

        if(isset($chgt->lots[1])) {
            $chgt->lots[1]->campagne = $lots[1]->campagne;
            $chgt->lots[1]->numero_archive = $lots[1]->numero_archive;
            $chgt->lots[1]->numero_dossier = $lots[1]->numero_dossier;
            $chgt->lots[1]->unique_id = $lots[1]->unique_id;
            $chgt->lots[1]->id_document_provenance = $lots[1]->id_document_provenance;
            $chgt->lots[1]->id_document_affectation = $lots[1]->id_document_affectation;
            $chgt->lots[1]->affectable = $lots[1]->affectable;
            $chgt->changement_affectable = $lots[1]->affectable;
            $chgt->lots[1]->statut = null;
            if(!$chgt->lots[1]->affectable) {
                $chgt->lots[1]->statut = Lot::STATUT_NONAFFECTABLE;
            }
            if(preg_match("/a$/", $chgt->lots[1]->numero_archive)) {
                $renameLot = $chgt->lots[1]->unique_id;
            }
            if($lots[1]->id_document_affectation && $lots[1]->document_ordre > 1) {
                $documentOrdresToRewrite[] = $lots[1]->unique_id;
            }
        }

        if($chgt->save(false)) {
            echo "$chgt->_id;chgtdenom:regenerate-lots;Sauvegardé $chgt->_rev\n";
        }

        if($renameLot) {
            foreach(LotsClient::getInstance()->getDocumentsIdsByOrdre($chgt->identifiant, $renameLot) as $id) {
                $doc = DeclarationClient::getInstance()->find($id);

                foreach($doc->lots as $lot) {
                    if($lot->unique_id != $renameLot) {
                        continue;
                    }
                    if($lot->produit_hash != $chgt->lots[1]->produit_hash) {
                        continue;
                    }
                    $lot->numero_archive = preg_replace('/a{1}$/', 'c', $lot->numero_archive);
                    echo $doc->_id.";chgtdenom:regenerate-lots;Réécriture du numéro de lot $renameLot en ".$lot->unique_id."\n";
                    if($doc->save(false)) {
                        echo "$doc->_id;chgtdenom:regenerate-lots;Sauvegardé $doc->_rev\n";
                    }
                }

            }
        }

    }

}

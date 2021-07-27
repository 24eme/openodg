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
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
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

        $lotOrigine = $chgt->getLotOrigine();

        $chgt->origine_millesime = $lotOrigine->millesime;
        $chgt->origine_volume = $lotOrigine->volume;
        $chgt->origine_specificite = $lotOrigine->specificite;
        $chgt->origine_produit_hash = $lotOrigine->produit_hash;
        $chgt->origine_cepages = $lotOrigine->cepages;
        $chgt->origine_produit_libelle = $lotOrigine->produit_libelle;
        $chgt->origine_numero_logement_operateur = $lotOrigine->numero_logement_operateur;
        $chgt->origine_affectable = $lotOrigine->affectable;

        $lots = $chgt->lots->toJson();

        if(count($lots) == 1) {
            $documentOrdresToRewrite[] = $lots[0]->unique_id;
            $lots[0]->document_ordre = sprintf("%0d2", 99);
            $lots[1] = $lots[0];
            $lots[0] = null;
        }
        $chgt->campagne = null;
        $chgt->getCampagne();
        $chgt->generateLots();

        if($lots[0]) {
            $chgt->lots[0]->document_ordre = $lots[0]->document_ordre;
            $chgt->lots[0]->campagne = $lots[0]->campagne;
            $chgt->lots[0]->numero_archive = $lots[0]->numero_archive;
            $chgt->lots[0]->numero_dossier = $lots[0]->numero_dossier;
            $chgt->lots[0]->unique_id = $lots[0]->unique_id;
            $chgt->lots[0]->id_document_provenance = $lots[0]->id_document_provenance;
            $chgt->lots[0]->id_document_affectation = $lots[0]->id_document_affectation;
        }

        if(isset($chgt->lots[1])) {
            $chgt->lots[1]->campagne = $lots[1]->campagne;
            $chgt->lots[1]->numero_archive = $lots[1]->numero_archive;
            $chgt->lots[1]->numero_dossier = $lots[1]->numero_dossier;
            $chgt->lots[1]->unique_id = $lots[1]->unique_id;
            $chgt->lots[1]->id_document_provenance = $lots[1]->id_document_provenance;
            $chgt->lots[1]->id_document_affectation = $lots[1]->id_document_affectation;
            if(preg_match("/a$/", $lots[1]->numero_archive)) {
                $renameLot = $lots[1]->unique_id;
            }
            if($lots[1]->id_document_affectation && $lots[1]->document_ordre > 1) {
                $documentOrdresToRewrite[] = $lots[1]->unique_id;
            }
        }

        $chgt->save(false);

        foreach($documentOrdresToRewrite as $uniqueId) {
            LotsClient::getInstance()->updateAndSaveDocumentsOrdres($chgt->identifiant, $uniqueId);
            echo $chgt->_id.";Mise à jour des documents d'ordre du lot $uniqueId, car il y a sans doute un trou dans les documents d'ordre\n";
        }

        if($renameLot) {
            foreach(LotsClient::getInstance()->getDocumentsIds($chgt->identifiant, $renameLot) as $id) {
                $doc = DeclarationClient::getInstance()->find($id);
                $lot = $doc->getLot($renameLot);
                $lot->numero_archive = preg_replace('/a{1}$/', 'b', $lot->numero_archive);
                $doc->save(false);
                echo $doc->_id.";Réécriture du numéro de lot $renameLot en ".$lot->unique_id."\n";
            }
        }

    }

}

<?php

class ImportLotsOCIATask extends importOperateurIACsvTask
{
  const CSV_NUMERO_ECHANTILLON = 0;
  const CSV_RAISON_SOCIALE = 2;
  const CSV_CVI = 3;
  const CSV_PRODUIT = 6;
  const CSV_MILLESIME = 7;
  const CSV_DATE_PRELEVEMENT = 9;
  const CSV_TYPE_DECLARATION = 10;
  const CSV_LOGEMENT = 11;
  const CSV_VOLUME = 12;
  const CSV_NUM_LOT = 13;
  const CSV_TYPE_CONTROLE = 14;

  const CSV_SYNTHESE_PRODUIT = 11;
  const CSV_SYNTHESE_MILLESIME = 12;
  const CSV_SYNTHESE_NUM_LOT = 13;
  const CSV_SYNTHESE_LOGEMENT = 14;
  const CSV_SYNTHESE_VOLUME = 15;
  const CSV_SYNTHESE_DATE_DECLARATION = 21;

  protected $date;
  protected $produits;
  protected $cepages;
  protected $synthese;

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "Fichier csv pour l'import"),
            new sfCommandArgument('csvsynthese', sfCommandArgument::OPTIONAL, "Fichier csv de la synthèse des lots pour complément d'info pour l'import"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'lots-oc-ia';
        $this->briefDescription = 'Import des lots oc ia (via un csv)';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $this->initProduitsCepages();

        $this->initSynthese($arguments['csvsynthese']);

        $document = null;
        $ligne = 0;
        $nb = 0;
        foreach(file($arguments['csv']) as $line) {
            $ligne++;
            $line = str_replace("\n", "", $line);
            $data = str_getcsv($line, ';');

            if (!$data) {
              continue;
            }

            $etablissement = $this->identifyEtablissement($data[self::CSV_RAISON_SOCIALE], $data[self::CSV_CVI], null);
            if (!$etablissement) {
               echo "WARNING;établissement non trouvé ".$data[self::CSV_RAISON_SOCIALE].";pas d'import;$line\n";
               continue;
            }

            $produitKey = $this->clearProduitKey(KeyInflector::slugify($this->alias($data[self::CSV_PRODUIT])));
            if (!isset($this->produits[$produitKey])) {
              echo "WARNING;produit non trouvé ".$data[self::CSV_PRODUIT]." ($produitKey);pas d'import;$line\n";
              continue;
            }

            if(!$data[self::CSV_DATE_PRELEVEMENT]) {
                echo "Warning;pas de date;$line\n";
                continue;
            }

            $numeroLot = null;
            if(isset($data[self::CSV_NUM_LOT])) {
                $numeroLot = trim($data[self::CSV_NUM_LOT]);
            }
            $logement = null;
            if(isset($data[self::CSV_LOGEMENT])) {
                $logement = trim($data[self::CSV_LOGEMENT]);
            }

            $dataAugmented = [];
            $dataAugmented['etablissement'] = $etablissement;
            $dataAugmented['date_prelevement'] = (preg_match('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/', trim($data[self::CSV_DATE_PRELEVEMENT]), $m))? $m[3].'-'.$m[2].'-'.$m[1] : null;
            $dataAugmented['date_declaration'] = $dataAugmented['date_prelevement'];
            $dataAugmented['produit'] = $this->produits[$produitKey];
            $dataAugmented['millesime'] = preg_match('/^[0-9]{4}$/', trim($data[self::CSV_MILLESIME]))? trim($data[self::CSV_MILLESIME])*1 : null;
            $dataAugmented['volume'] = str_replace(',','.',trim($data[self::CSV_VOLUME])) * 1;
            $dataAugmented['numero_logement_operateur'] = trim(preg_replace('#(^/|/$)#', "", trim($logement.' / '.$numeroLot)));
            $dataAugmented['numero_dossier'] = sprintf("%05d", explode("-", $data[self::CSV_NUMERO_ECHANTILLON])[0]);
            $dataAugmented['numero_archive'] = sprintf("%05d", explode("-", $data[self::CSV_NUMERO_ECHANTILLON])[1]);
            if ($this->synthese) {
                $syntheseKey = $this->makeSyntheseKey($dataAugmented['produit'], $dataAugmented['millesime'], $dataAugmented['volume'], $dataAugmented['numero_logement_operateur']);
                if (isset($this->synthese[$syntheseKey])) {
                    if (isset($this->synthese[$syntheseKey][self::CSV_SYNTHESE_DATE_DECLARATION]) && preg_match('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/', trim($this->synthese[$syntheseKey][self::CSV_SYNTHESE_DATE_DECLARATION]), $m)) {
                        $dataAugmented['date_declaration'] = $m[3].'-'.$m[2].'-'.$m[1];
                    }
                }
            }

            $typeControle = $data[self::CSV_TYPE_CONTROLE];

            if($typeControle == "VEX") {
                $this->importVracExport($data, $dataAugmented);
            }

            if($typeControle == "ALE") {
                $this->importAleatoire($data, $dataAugmented, TourneeClient::TYPE_TOURNEE_LOT_ALEATOIRE);
            }

            if($typeControle == "ALR") {
                $this->importAleatoire($data, $dataAugmented, TourneeClient::TYPE_TOURNEE_LOT_ALEATOIRE_RENFORCE);
            }

            if($typeControle == "SUP") {
                $this->importAleatoire($data, $dataAugmented, TourneeClient::TYPE_TOURNEE_LOT_SUPPLEMENTAIRE);
            }

            if($typeControle == "NCI") {
                $this->importPMCNC($data, $dataAugmented);
            }

            if($typeControle == "NCO") {
                // Rien besoin de faire l'import des dégustations suffit
            }
        }
    }

    protected function initSynthese($filename) {
        $this->synthese = [];
        if (!$filename) {
            return;
        }
        if (!file_exists($filename)) {
            return;
        }
        foreach(file($filename) as $line) {
            $line = str_replace("\n", "", $line);
            $data = str_getcsv($line, ';');
            if (!$data) {
              continue;
            }
            $produitKey = $this->clearProduitKey(KeyInflector::slugify($this->alias($data[self::CSV_SYNTHESE_PRODUIT])));
            $produit = $this->produits[$produitKey];
            $millesime = preg_match('/^[0-9]{4}$/', trim($data[self::CSV_SYNTHESE_MILLESIME]))? trim($data[self::CSV_SYNTHESE_MILLESIME])*1 : null;
            $volume = str_replace(',','.',trim($data[self::CSV_SYNTHESE_VOLUME]));
            if (is_numeric($volume)) {
                $volume = $volume *1;
            }
            $numeroLot = null;
            if(isset($data[self::CSV_SYNTHESE_NUM_LOT])) {
                $numeroLot = trim($data[self::CSV_SYNTHESE_NUM_LOT]);
            }
            $logement = null;
            if(isset($data[self::CSV_SYNTHESE_LOGEMENT])) {
                $logement = trim($data[self::CSV_SYNTHESE_LOGEMENT]);
            }
            $logementOperateur = trim(preg_replace('#(^/|/$)#', "", trim($logement.' / '.$numeroLot)));

            $syntheseKey = $this->makeSyntheseKey($produit, $millesime, $volume, $logementOperateur);
            $this->synthese[$syntheseKey] =$data;
        }
    }

    protected function makeSyntheseKey($produit, $millesime, $volume, $logementOperateur) {
        $produitLibelle = '';
        if ($produit && is_object($produit)) {
            $produitLibelle = $produit->getLibelleFormat();
        }
        return KeyInflector::slugify($produitLibelle.'-'.$millesime.'-'.$volume.'-'.$logementOperateur);
    }

    protected function importVracExport($data, $dataAugmented) {
        $etablissement = $dataAugmented['etablissement'];
        $campagne = ConfigurationClient::getInstance()->buildCampagne($dataAugmented['date_declaration']);
        $transaction = TransactionClient::getInstance()->findByIdentifiantAndDateOrCreateIt($etablissement->identifiant, $campagne, $dataAugmented['date_declaration']);
        $transaction->save();
        $lot = $transaction->addLot();
        $lot->date = $dataAugmented['date_declaration'];
        $lot->produit_hash = $dataAugmented['produit']->getHash();
        $lot->produit_libelle = $dataAugmented['produit']->getLibelleFormat();
        $lot->millesime = $dataAugmented['millesime'];
        $lot->volume = $dataAugmented['volume'];
        $lot->numero_logement_operateur = $dataAugmented['numero_logement_operateur'];
        $lot->affectable = true;
        $transaction->save();
        $transaction->validate($dataAugmented['date_declaration']);
        $transaction->validateOdg($dataAugmented['date_declaration']);
        $transaction->save();

        echo $transaction->_id."\n";
    }

    protected function importAleatoire($data, $dataAugmented, $initial_type) {
        $etablissement = $dataAugmented['etablissement'];
        $campagne = ConfigurationClient::getInstance()->buildCampagne($dataAugmented['date_prelevement']);
        $tournee = TourneeClient::getInstance()->findOrCreate($dataAugmented['date_prelevement'], "OIVC");
        $tournee->save();

        $lot = $tournee->add('lots')->add();
        $lot->id_document = $tournee->_id;
        $lot->declarant_identifiant = $etablissement->identifiant;
        $lot->declarant_nom = $etablissement->nom;
        $lot->initial_type = $initial_type;
        $lot->date = $dataAugmented['date_prelevement'];
        $lot->produit_hash = $dataAugmented['produit']->getHash();
        $lot->produit_libelle = $dataAugmented['produit']->getLibelleFormat();
        $lot->millesime = $dataAugmented['millesime'];
        $lot->volume = ($dataAugmented['volume']) ? $dataAugmented['volume'] : null;
        $lot->numero_logement_operateur = $dataAugmented['numero_logement_operateur'];
        $lot->affectable = true;
        $lot->setIsPreleve($dataAugmented['date_prelevement']);
        $tournee->save();
        $tournee->archiverLot($tournee->numero_archive);
        $tournee->etape = DegustationEtapes::ETAPE_VISUALISATION;
        $tournee->save();
        echo $tournee->_id."\n";
    }

    protected function importPMCNC($data, $dataAugmented) {
        $etablissement = $dataAugmented['etablissement'];
        $campagne = ConfigurationClient::getInstance()->buildCampagne($dataAugmented['date_declaration']);

        $lots = MouvementLotView::getInstance()->find($etablissement->identifiant, array('volume' => $dataAugmented['volume'], 'produit_hash' => $dataAugmented['produit']->getHash(), 'millesime' =>  $dataAugmented['millesime'], 'numero_logement_operateur' => $dataAugmented['numero_logement_operateur'], 'initial_type' => "PMC", 'statut' => Lot::STATUT_NONCONFORME), false);

        $lotOrigine = null;
        if(count($lots) == 1) {
            $lotOrigine = $lots[0];
        }

        if(!$lotOrigine) {
            $lots = MouvementLotView::getInstance()->find($etablissement->identifiant, array('volume' => $dataAugmented['volume'], 'produit_hash' => $dataAugmented['produit']->getHash(), 'millesime' =>  $dataAugmented['millesime'], 'initial_type' => "PMC", 'statut' => Lot::STATUT_NONCONFORME), false);

            if(count($lots) == 1) {
                $lotOrigine = $lots[0];
            }
        }

        if(!$lotOrigine) {
            foreach($lots as $key => $lot) {
                if($dataAugmented['numero_logement_operateur'] && !preg_match("|^".$dataAugmented['numero_logement_operateur']." |", $lot->numero_logement_operateur)) {
                    unset($lots[$key]);
                }
            }
            $lots =  array_values($lots);
            if(count($lots) == 1) {
                $lotOrigine = $lots[0];
            }
        }

        if(!$lotOrigine && $dataAugmented['numero_logement_operateur']) {
            $lots = MouvementLotView::getInstance()->find($etablissement->identifiant, array('produit_hash' => $dataAugmented['produit']->getHash(), 'millesime' =>  $dataAugmented['millesime'], 'numero_logement_operateur' => $dataAugmented['numero_logement_operateur'], 'initial_type' => "PMC", 'statut' => Lot::STATUT_NONCONFORME), false);
            if(count($lots) == 1) {
                $lotOrigine = $lots[0];
            }
        }
        $pmcnc = PMCNCClient::getInstance()->findByIdentifiantAndDateOrCreateIt($etablissement->identifiant, $campagne, $dataAugmented['date_declaration']." 00:00:00");
        $pmcnc->save();

        if($lotOrigine) {
            $lotOrigineObject = LotsClient::getInstance()->findByUniqueId($lotOrigine->declarant_identifiant, $lotOrigine->unique_id, $lotOrigine->document_ordre);

            $lotDef = PMCLot::freeInstance(new PMCNC());
            foreach($lotOrigineObject->getFields() as $key => $value) {
                if ($lotDef->getDefinition()->exist($key)) {
                    continue;
                }
                unset($lotOrigine->{$key});
            }
            $lot = $pmcnc->lots->add(null, $lotOrigine);
        } else {
            $lot = $pmcnc->addLot();
            $lot->id_document_provenance = $pmcnc->_id;
            $lot->document_ordre = "01";
        }
        $lot->id_document = $pmcnc->_id;
        $lot->updateDocumentDependances();
        $lot->date = $dataAugmented['date_declaration'];
        $lot->produit_hash = $dataAugmented['produit']->getHash();
        $lot->produit_libelle = $dataAugmented['produit']->getLibelleFormat();
        $lot->millesime = $dataAugmented['millesime'];
        $lot->document_ordre = "03";
        $lot->volume = $dataAugmented['volume'];
        $lot->numero_logement_operateur = $dataAugmented['numero_logement_operateur'];
        $lot->affectable = true;
        $pmcnc->save();
        $lot->updateDocumentDependances();
        $pmcnc->validate($dataAugmented['date_declaration']);
        $pmcnc->validateOdg($dataAugmented['date_declaration']);
        $pmcnc->save();

        echo $pmcnc->_id."\n";
    }

    protected function alias($produit) {

        return $produit;
    }

}

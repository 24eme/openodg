<?php

class ImportLotsOCIATask extends importOperateurIACsvTask
{
  const CSV_NUMERO_ECHANTILLON = 0;
  const CSV_RAISON_SOCIALE = 2;
  const CSV_CVI = 3;
  const CSV_PRODUIT = 6;
  const CSV_MILLESIME = 7;
  const CSV_DATE_PRELEVEMENT = 9;
  const CSV_LOGEMENT = 11;
  const CSV_VOLUME = 12;
  const CSV_NUM_LOT = 13;
  const CSV_TYPE_CONTROLE = 14;

  protected $date;
  protected $produits;
  protected $cepages;

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "Fichier csv pour l'import"),
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
            $dataAugmented['produit'] = $this->produits[$produitKey];
            $dataAugmented['millesime'] = preg_match('/^[0-9]{4}$/', trim($data[self::CSV_MILLESIME]))? trim($data[self::CSV_MILLESIME])*1 : null;
            $dataAugmented['volume'] = str_replace(',','.',trim($data[self::CSV_VOLUME])) * 1;
            $dataAugmented['numero_logement_operateur'] = trim($logement.' '.$numeroLot);
            $dataAugmented['numero_dossier'] = sprintf("%05d", explode("-", $data[self::CSV_NUMERO_ECHANTILLON])[0]);
            $dataAugmented['numero_archive'] = sprintf("%05d", explode("-", $data[self::CSV_NUMERO_ECHANTILLON])[1]);

            $typeControle = $data[self::CSV_TYPE_CONTROLE];

            if($typeControle == "VEX") {
                $this->importVracExport($data, $dataAugmented);
            }

            if($typeControle == "ALE") {
                $this->importAleatoire($data, $dataAugmented, 'Degustation:aleatoire');
            }

            if($typeControle == "ALR") {
                $this->importAleatoire($data, $dataAugmented, 'Degustation:aleatoire_renforce');
            }
        }
    }

    protected function importVracExport($data, $dataAugmented) {
        $etablissement = $dataAugmented['etablissement'];
        $campagne = ConfigurationClient::getInstance()->buildCampagne($dataAugmented['date_prelevement']);
        $transaction = TransactionClient::getInstance()->findByIdentifiantAndDateOrCreateIt($etablissement->identifiant, $campagne, $dataAugmented['date_prelevement']);
        $transaction->numero_archive = $dataAugmented['numero_dossier'];
        $transaction->save();
        $lot = $transaction->addLot();
        $lot->date = $dataAugmented['date_prelevement'];
        $lot->numero_dossier = $dataAugmented['numero_dossier'];
        $lot->numero_archive = $dataAugmented['numero_archive'];
        $lot->produit_hash = $dataAugmented['produit']->getHash();
        $lot->produit_libelle = $dataAugmented['produit']->getLibelleFormat();
        $lot->millesime = $dataAugmented['millesime'];
        $lot->volume = $dataAugmented['volume'];
        $lot->numero_logement_operateur = $dataAugmented['numero_logement_operateur'];
        $lot->affectable = true;
        $transaction->save();
        $transaction->validate($dataAugmented['date_prelevement']);
        $transaction->validateOdg($dataAugmented['date_prelevement']);
        $transaction->save();

        echo $transaction->_id."\n";
    }

    protected function importAleatoire($data, $dataAugmented, $initial_type) {
        $etablissement = $dataAugmented['etablissement'];
        $campagne = ConfigurationClient::getInstance()->buildCampagne($dataAugmented['date_prelevement']);
        $tournee = TourneeClient::getInstance()->findOrCreate($dataAugmented['date_prelevement'], "OIVC");
        $tournee->numero_archive = $dataAugmented['numero_dossier'];
        $tournee->save();

        $lot = $tournee->add('lots')->add();
        $lot->id_document = $tournee->_id;
        $lot->declarant_identifiant = $etablissement->identifiant;
        $lot->declarant_nom = $etablissement->nom;
        $lot->initial_type = $initial_type;
        $lot->date = $dataAugmented['date_prelevement'];
        $lot->numero_dossier = $dataAugmented['numero_dossier'];
        $lot->numero_archive = $dataAugmented['numero_archive'];
        $lot->produit_hash = $dataAugmented['produit']->getHash();
        $lot->produit_libelle = $dataAugmented['produit']->getLibelleFormat();
        $lot->millesime = $dataAugmented['millesime'];
        $lot->volume = ($dataAugmented['volume']) ? $dataAugmented['volume'] : 1;
        $lot->numero_logement_operateur = $dataAugmented['numero_logement_operateur'];
        $lot->affectable = true;
        $lot->setIsPreleve($dataAugmented['date_prelevement']);
        $tournee->etape = DegustationEtapes::ETAPE_VISUALISATION;
        $tournee->save();
        echo $tournee->_id."\n";
    }

    protected function alias($produit) {

        return $produit;
    }

}

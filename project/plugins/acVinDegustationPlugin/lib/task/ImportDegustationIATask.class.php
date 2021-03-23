<?php

class ImportDegustationIATask extends ImportLotsIATask
{

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
        $this->name = 'degustations-ia';
        $this->briefDescription = 'Import des dégustations (via un csv)';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $this->initProduitsCepages();

        $this->etablissements = EtablissementAllView::getInstance()->getAll();

        $config = ConfigurationClient::getCurrent();
        $ligne=0;
        $degustation = null;
        foreach(file($arguments['csv']) as $line) {
          $ligne++;
          $line = str_replace("\n", "", $line);
          $data = str_getcsv($line,';');
          if (!$data) {
            continue;
          }

          $degustation_date = $this->formatDate($data[self::CSV_DATE_COMMISSION]);

          if(!$degustation_date){
              continue;
          }

          $campagne = str_replace("/", "-", trim($data[self::CSV_CAMPAGNE]));

          if($campagne < "2019-2020") {
              continue;
          }

          $produitKey=null;
          if (isset($data[self::CSV_APPELLATION])){
            $produitKey = $this->clearProduitKey(KeyInflector::slugify(trim($data[self::CSV_APPELLATION])." ".trim($data[self::CSV_COULEUR])));
          }

          if (!isset($this->produits[$produitKey])) {
            echo "WARNING;produit non trouvé;pas d'import;$line\n";
            continue;
          }
          $produit = $this->produits[$produitKey];

          $etablissement = $this->identifyEtablissement($data);
            if (!$etablissement) {
               echo "WARNING;établissement non trouvé ".$data[self::CSV_RAISON_SOCIALE].";pas d'import;$line\n";
               continue;
            }

          $numeroDossier = sprintf("%05d", trim($data[self::CSV_NUM_DOSSIER]));
          $numeroLot = sprintf("%05d", trim($data[self::CSV_NUM_LOT_ODG]));

          $mouvementsLot = MouvementLotHistoryView::getInstance()->getMouvements($etablissement->identifiant, $campagne, $numeroDossier, $numeroLot, "01", Lot::STATUT_AFFECTABLE)->rows;

          if(!count($mouvementsLot)) {
              continue;
          }

          foreach($mouvementsLot as $mouvementLot) {
              break;
          }

          $document = DeclarationClient::getInstance()->find($mouvementLot->id, acCouchdbClient::HYDRATE_DOCUMENT);
          $lot = $document->get($mouvementLot->value->lot_hash);

          $degustation = null;
          foreach(DegustationClient::getInstance()->startkey(DegustationClient::TYPE_COUCHDB."-".str_replace("-", "", $degustation_date)."999999")->endkey(DegustationClient::TYPE_COUCHDB."-".str_replace("-", "", $degustation_date)."000000")->descending(true)->limit(1)->execute(acCouchdbClient::HYDRATE_ON_DEMAND)->getIds() as $id) {
              $degustation = DegustationClient::getInstance()->find($id);
              break;
          }

          if(!$degustation) {
              $lot->affectable = false;
              $document->save();
              continue;
          }

          $lot = $degustation->addLot($lot, false);
          $lot->email_envoye = $degustation->date;

          if($data[self::CSV_STATUT] == "Conforme") {
             $lot->statut = Lot::STATUT_CONFORME;
             $lot->conformite = Lot::CONFORMITE_CONFORME;
          }

          if($data[self::CSV_STATUT] == "Non Conforme") {
             $lot->statut = Lot::STATUT_NONCONFORME;
             $lot->conformite = Lot::CONFORMITE_NONCONFORME_MINEUR;
          }

          if($data[self::CSV_STATUT] == "Déclassé") {
             $lot->statut = Lot::STATUT_NONCONFORME;
             $lot->conformite = Lot::CONFORMITE_NONCONFORME_MINEUR;
          }

          if($data[self::CSV_STATUT] == "Revendiqué C") {
             $lot->statut = Lot::STATUT_NONCONFORME;
             $lot->conformite = Lot::CONFORMITE_NONCONFORME_MINEUR;
          }

          $degustation->save();
        }

        foreach(DegustationClient::getInstance()->getLotsPrelevables() as $lot) {
            if(!preg_match("/^CHGT/", $lot->id_document) && $lot->date >= '2020-11-01') {
                continue;
            }
            $doc = DeclarationClient::getInstance()->find($lot->id_document);
            $doc->getLot($lot->unique_id)->affectable = false;
            $doc->save();
        }

      }

    public function formatDate($date){
        if(!$date) {
            return null;
        }
      $jour=$date[0].$date[1];
      $mois=$date[3].$date[4];
      $annee=$date[6].$date[7].$date[8].$date[9];
      $d= $annee.'-'.$mois.'-'.$jour;
      return $d;
    }


}

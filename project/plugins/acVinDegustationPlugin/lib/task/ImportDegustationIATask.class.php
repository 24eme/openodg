<?php

class ImportDegustationIATask extends ImportLotsIATask
{

    public static $correspondancesStatuts = array(
      "Conforme" => Lot::STATUT_CONFORME,
      "Déclassé" => Lot::STATUT_NONCONFORME,
      "Non Conforme" => Lot::STATUT_NONCONFORME,
      "Prélevé A" => Lot::STATUT_PRELEVE, //Prélevé Anonimisé
      "Prélevé NA" => Lot::STATUT_PRELEVE, //Prélevé Non Anonimisé
      "Prévu" => Lot::STATUT_ATTENTE_PRELEVEMENT,
      "Revendiqué NC" => Lot::STATUT_NONCONFORME
    );

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
        $commissions = DegustationConfiguration::getInstance()->getCommissions();
        $degustation = null;
        $ligne=0;
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

          $campagne = null;
          if(isset($data[self::CSV_CAMPAGNE])){
            $campagne = preg_replace('/\/.*/', '', trim($data[self::CSV_CAMPAGNE]));
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
          $date_validation = (preg_match('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/', trim($data[self::CSV_DATE_VALIDATION]), $m))? $m[3].'-'.$m[2].'-'.$m[1] : null;

          $numeroDossier = sprintf("%05d", trim($data[self::CSV_NUM_DOSSIER]));
          $numeroLot = sprintf("%05d", trim($data[self::CSV_NUM_LOT_ODG]));
          $statut = null;
          if(isset($data[self::CSV_STATUT])){
            $statut = trim($data[self::CSV_STATUT]);
         }
         if (!isset(self::$correspondancesStatuts[$statut])) {
            echo "WARNING;statut inconnu ".$statut.";pas d'import;$line\n";
            continue;
         }
         $statut = self::$correspondancesStatuts[$statut];

         if($statut)

          $mouvements = MouvementLotView::getInstance()->getByDeclarantIdentifiant($etablissement->identifiant, $campagne);

          $mouvement = null;
          foreach ($mouvements->rows as $mvt) {
              if($mvt->value->numero_dossier != $numeroDossier) {
                  continue;
              }
              if($mvt->value->numero_archive != $numeroLot) {
                  continue;
              }
              if($mvt->value->produit_hash != $produit->getHash()) {
                  continue;
              }

              $mouvement = $mvt;
              break;
          }

          if(!$mouvement) {
              echo "WARNING;Lot non trouvé dans la drev;".$line."\n";
              continue;
          }

          $newDegustation = new Degustation();
          $newDegustation->date=$degustation_date;
          $newDegustation->lieu =$commissions[0];   //choisir un lieu car pas dispo dans le csv
          $newDegustation->campagne=$campagne;
          $newDegustation->constructId();
          $newDegustation->validation = $degustation_date;

          if(!$degustation || $newDegustation->_id != $degustation->_id) {
              $degustation = acCouchdbManager::getClient()->find($newDegustation->_id);
              if($degustation) { $degustation->delete(); $degustation = null; }
          }

          if(!$degustation) {
              $degustation = $newDegustation;
          }

          $lot = $degustation->addLot($mouvement->value);
          $lot->numero_table = 1; // Car on ne l'a pas
          $lot->statut = $statut;

          if($lot->statut == Lot::STATUT_CONFORME) {
              $lot->conformite = Lot::CONFORMITE_CONFORME;
          }

          //$degustation->generateMouvementsLots();
          $degustation->save();
        }
      }

    public function formatDate($date){
        if(!$date) {
            return null;
        }
      $jour=$date[0].$date[1];
      $mois=$date[3].$date[4];
      $annee=$date[6].$date[7].$date[8].$date[9];
      $d= $annee.'-'.$mois.'-'.$jour." 01:00";
      return $d;
    }


}

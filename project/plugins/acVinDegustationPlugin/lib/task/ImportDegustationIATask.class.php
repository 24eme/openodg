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
        $commissions = DegustationConfiguration::getInstance()->getCommissions();
        $date_degustation_precedente= null;
        $ligne=0;
        foreach(file($arguments['csv']) as $line) {
          $ligne++;
          $line = str_replace("\n", "", $line);
          $data = str_getcsv($line,';');
          if (!$data) {
            continue;
          }

          if(!isset($data[self::CSV_DATE_COMMISSION])){
              continue;
          }

          $degustation_date = $this->formatDate($data[self::CSV_DATE_COMMISSION]);

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

          $statut = null;
          if(isset($data[self::CSV_STATUT])){
            $statut = trim($data[self::CSV_STATUT]);
         }

         if (!isset(self::$correspondancesStatuts[$statut])) {
            echo "WARNING;statut inconnu ".$statut.";pas d'import;$line\n";
            continue;
         }

         $statut = (is_array(self::$correspondancesStatuts[$statut]))? self::$correspondancesStatuts[$statut][$preleve] : self::$correspondancesStatuts[$statut];

          $etablissement = $this->identifyEtablissement($data);
            if (!$etablissement) {
               echo "WARNING;établissement non trouvé ".$data[self::CSV_RAISON_SOCIALE].";pas d'import;$line\n";
               continue;
            }
          $date_validation = (preg_match('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/', trim($data[self::CSV_DATE_VALIDATION]), $m))? $m[3].'-'.$m[2].'-'.$m[1] : null;

          $numero = null;
          if (isset($data[self::CSV_NUM_LOT_OPERATEUR])){
            $numero = trim($data[self::CSV_NUM_LOT_OPERATEUR]);
          }

          $drevs=MouvementLotView::getInstance()->getByDeclarantIdentifiant($etablissement->identifiant,$campagne,$statut);

          $drevs = json_decode(json_encode($drevs), true);
          foreach ($drevs["rows"] as $drev) {
            if ($drev["value"]["numero_cuve"] != $numero){
                continue;
            }
            $origine_mouvement=$drev["value"]["origine_mouvement"];
            $id = $drev["value"]["origine_document_id"];
            $declarant_identifiant=$drev["value"]["declarant_identifiant"];
            $volume=$drev["value"]["volume"];
            $millesime=$drev["value"]["millesime"];
            $numero_archive=$drev["value"]["numero_archive"];
            $numero_dossier=$drev["value"]["numero_dossier"];
            $destination_type=$drev["value"]["destination_type"];
            $destination_date=$drev["value"]["destination_date"];
            $details=$drev["value"]["details"];
          }

          $newDegustation = new Degustation();
          $newDegustation->date=$degustation_date;
          $newDegustation->lieu =$commissions[0];   //choisir un lieu car pas dispo dans le csv
          $newDegustation->campagne=$campagne;
          $newDegustation->constructId();

          if(!$degustation || $newDegustation->_id != $degustation->_id) {
              $degustation = acCouchdbManager::getClient()->find($newDegustation->_id);
          }

          if(!$degustation) {
              $degustation = $newDegustation;
          }

          $lot = $degustation->addLot();
          $lot->date=$date_validation;
          $lot->id_document=$id;
          $lot->numero_dossier=$numero_dossier;
          $lot->numero_archive=$numero_archive;
          $lot->numero_cuve=$numero;
          $lot->millesime=$millesime;
          $lot->volume=$volume;
          $lot->destination_type=$destination_type;
          $lot->destination_date=$destination_date;
          $lot->produit_hash=$produit->getHash();
          $lot->produit_libelle=$produit->getLibelleFormat();
          if (isset($data[self::CSV_RAISON_SOCIALE])){
            $lot->declarant_identifiant=$etablissement->identifiant;
            $lot->declarant_nom=$data[self::CSV_RAISON_SOCIALE];
          }
          $lot->origine_mouvement=$origine_mouvement;
          $lot->details=$details;
          $lot->statut=$statut;

          $degustation->save();
        }
      }

    public function formatDate($date){
      $jour=$date[0].$date[1];
      $mois=$date[3].$date[4];
      $annee=$date[6].$date[7].$date[8].$date[9];
      $d= $annee.'-'.$mois.'-'.$jour." 01:00";
      return $d;
    }


}

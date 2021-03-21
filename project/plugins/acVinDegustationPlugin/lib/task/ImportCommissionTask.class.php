<?php

class ImportCommissionIATask extends ImportLotsIATask
{
    const CSV_DATE_COMMISSION = 0;
    const CSV_ID= 1;
    const CSV_CAMPAGNE = 2;
    const CSV_MILLESIME = 3;
    const CSV_NOM_RESPONSABLE = 4;
    const CSV_LIEU_NOM = 5;
    const CSV_LIEU_ADRESSE = 6;
    const CSV_LIEU_CODE_POSTAL = 7;
    const CSV_LIEU_COMMUNE = 8;
    const CSV_TYPE_LIGNE = 9;
    const CSV_RAISON_SOCIALE = 10;
    const CSV_NOM = 10;
    const CSV_APPELLATION = 11;
    const CSV_COULEUR = 12;
    const CSV_VOLUME = 14;
    const CSV_NUM_LOT_OPERATEUR = 15;
    const CSV_NUMERO_ANONYMAT = 20;
    const CSV_RESULTAT = 21;
    const CSV_OBSERVATION = 22;
    const CSV_CVI = 99;

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
        $this->name = 'commissions-ia';
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

          if(!$degustation_date) {
              continue;
          }

          $date = $degustation_date." ".sprintf("%02d:%02d:00", preg_replace("/-.*$/", "", $data[self::CSV_ID]), preg_replace("/^.*-/", "", $data[self::CSV_ID]));

          $campagne = null;
          if(isset($data[self::CSV_CAMPAGNE])){
              $campagne = str_replace('/', '-', trim($data[self::CSV_CAMPAGNE]));
          }

          $newDegustation = new Degustation();
          $newDegustation->date=$date;
          $newDegustation->lieu = $data[self::CSV_LIEU_NOM]." — ".$data[self::CSV_LIEU_ADRESSE]." ".$data[self::CSV_LIEU_CODE_POSTAL]." ".$data[self::CSV_LIEU_COMMUNE];
          $newDegustation->campagne=$campagne;
          $newDegustation->constructId();
          $newDegustation->validation = $degustation_date;

          if(!$degustation || $newDegustation->_id != $degustation->_id) {
              if($degustation) {
                  $degustation->etape = DegustationEtapes::ETAPE_RESULTATS;
                  $degustation->save();
              }
              $degustation = acCouchdbManager::getClient()->find($newDegustation->_id);
              if($degustation) { $degustation->delete(); $degustation = null; }
          }

          if(!$degustation) {
              $degustation = $newDegustation;
          }

          if($data[self::CSV_TYPE_LIGNE] == "JURY") {
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

          $numeroCuve = $data[self::CSV_NUM_LOT_OPERATEUR];
          $volume = str_replace(',','.',trim($data[self::CSV_VOLUME])) * 1;
          $numeroTable = trim(explode(".", $data[self::CSV_NUMERO_ANONYMAT])[0]);
          $numeroAnonymat = trim(explode(".", $data[self::CSV_NUMERO_ANONYMAT])[1]);
          $resultat = $data[self::CSV_RESULTAT];

          $lot = MouvementLotView::getInstance()->find($etablissement->identifiant, array('volume' => $volume, 'numero_logement_operateur' => $numeroCuve, 'produit_hash' => $produit->getHash(), 'millesime' => $data[self::CSV_MILLESIME], 'statut' => Lot::STATUT_AFFECTABLE));

          if(!$lot) {
              $lot = MouvementLotView::getInstance()->find($etablissement->identifiant, array('volume' => $volume, 'numero_logement_operateur' => $numeroCuve, 'produit_hash' => $produit->getHash(), 'campagne' => $campagne, 'statut' => Lot::STATUT_AFFECTABLE));
          }

          if(!$lot) {
              $lot = MouvementLotView::getInstance()->find($etablissement->identifiant, array('numero_logement_operateur' => $numeroCuve, 'produit_hash' => $produit->getHash(), 'millesime' => $data[self::CSV_MILLESIME], 'statut' => Lot::STATUT_AFFECTABLE));
          }

          if(!$lot) {
              echo "ERROR;mouvement de lot d'origin non trouvé;$line\n";
              continue;
          }

          $lot = $degustation->addLot($lot, false);
          $lot->numero_table = $numeroTable;
          $lot->numero_anonymat = $numeroAnonymat;

          if($data[self::CSV_RESULTAT] == "C") {
             $lot->statut = Lot::STATUT_CONFORME;
             $lot->conformite = Lot::CONFORMITE_CONFORME;
          }

          if($data[self::CSV_RESULTAT] == "NC") {
             $lot->statut = Lot::STATUT_NONCONFORME;
             $lot->conformite = Lot::CONFORMITE_NONCONFORME_MINEUR;
          }

          $data[self::CSV_OBSERVATION] = trim($data[self::CSV_OBSERVATION]);
          if($data[self::CSV_OBSERVATION]) {
              $lot->observation = $data[self::CSV_OBSERVATION];
          }
        }

        if($degustation) {
            $degustation->etape = DegustationEtapes::ETAPE_RESULTATS;
            $degustation->save();
        }
      }

    public function formatDate($date){
        if(!$date) {
            return null;
        }
        if(!isset($date[9])) {
            return null;
        }
      $jour=$date[0].$date[1];
      $mois=$date[3].$date[4];
      $annee=$date[6].$date[7].$date[8].$date[9];
      $d= $annee.'-'.$mois.'-'.$jour;
      return $d;
    }

    protected function identifyEtablissement($data) {

        $key = KeyInflector::slugify(str_replace(" ", "", (isset($data[self::CSV_CVI]) ? $data[self::CSV_CVI] : "").$data[self::CSV_RAISON_SOCIALE].$data[self::CSV_NOM]));

        if(isset($this->etablissementsCache[$key])) {
            return $this->etablissementsCache[$key];
        }

        foreach ($this->etablissements as $etab) {
            if (isset($data[self::CSV_CVI]) && trim($data[self::CSV_CVI]) && $etab->key[EtablissementAllView::KEY_CVI] == trim($data[self::CSV_CVI])) {

                $this->etablissementsCache[$key] = EtablissementClient::getInstance()->find($etab->id, acCouchdbClient::HYDRATE_JSON);
                return $this->etablissementsCache[$key];
            }
            if (isset($data[self::CSV_RAISON_SOCIALE]) && trim($data[self::CSV_RAISON_SOCIALE]) && KeyInflector::slugify($etab->key[EtablissementAllView::KEY_NOM]) == KeyInflector::slugify(trim($data[self::CSV_RAISON_SOCIALE]))) {
                $this->etablissementsCache[$key] = EtablissementClient::getInstance()->find($etab->id, acCouchdbClient::HYDRATE_JSON);
                return $this->etablissementsCache[$key];
            }
            if (isset($data[self::CSV_RAISON_SOCIALE]) && trim($data[self::CSV_RAISON_SOCIALE]) && KeyInflector::slugify($etab->value[EtablissementAllView::VALUE_RAISON_SOCIALE]) == KeyInflector::slugify(trim($data[self::CSV_RAISON_SOCIALE]))) {
                $this->etablissementsCache[$key] = EtablissementClient::getInstance()->find($etab->id, acCouchdbClient::HYDRATE_JSON);
                return $this->etablissementsCache[$key];
            }
            if (isset($data[self::CSV_NOM]) && trim($data[self::CSV_NOM]) && KeyInflector::slugify($etab->key[EtablissementAllView::KEY_NOM]) == KeyInflector::slugify(trim($data[self::CSV_NOM]))) {
                $this->etablissementsCache[$key] = EtablissementClient::getInstance()->find($etab->id, acCouchdbClient::HYDRATE_JSON);
                return $this->etablissementsCache[$key];
            }
            if (isset($data[self::CSV_NOM]) && trim($data[self::CSV_NOM]) && KeyInflector::slugify($etab->value[EtablissementAllView::VALUE_RAISON_SOCIALE]) == KeyInflector::slugify(trim($data[self::CSV_NOM]))) {
                $this->etablissementsCache[$key] = EtablissementClient::getInstance()->find($etab->id, acCouchdbClient::HYDRATE_JSON);
                return $this->etablissementsCache[$key];
            }
        }
        return null;
    }


}

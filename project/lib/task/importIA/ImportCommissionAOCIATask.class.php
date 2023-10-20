<?php

class ImportCommissionA0CIATask extends ImportLotsIATask
{
    const CSV_ID= 1;
    const CSV_RESPONSABLE = 3;
    const CSV_DATE_COMMISSION = 7;
    const CSV_CAMPAGNE = 11;
    const CSV_LIEU_NOM = 9;
    const CSV_LIEU_ADRESSE = 14;
    const CSV_LIEU_CODE_POSTAL = 16;
    const CSV_LIEU_COMMUNE = 18;
    const CSV_NUMERO_ECHANTILLON = 19;
    const CSV_OPERATEUR = 20;
    const CSV_PRODUIT = 21;
    const CSV_VOLUME = 22;
    const CSV_NUM_LOT_OPERATEUR = 23;
    const CSV_NUMERO_TABLE = 26;
    const CSV_NUMERO_ANONYMAT = 27;
    const CSV_RESULTAT_LABO = 28;
    const CSV_RESULTAT_ORGANO = 31;

    const CSV_MILLESIME = 98;
    const CSV_NOM_RESPONSABLE = 4;
    const CSV_TYPE_LIGNE = 9;
    const CSV_NOM = 10;
    const CSV_COLLEGE = 11;
    const CSV_APPELLATION = 11;
    const CSV_COULEUR = 12;
    const CSV_OBSERVATION = 22;
    const CSV_CVI = 99;

    protected $dates = [];

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "Fichier csv pour l'import"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            new sfCommandOption('region', null, sfCommandOption::PARAMETER_REQUIRED, 'Force region', null),
        ));

        $this->namespace = 'import';
        $this->name = 'commissions-aoc-ia';
        $this->briefDescription = 'Import des dégustations (via un csv)';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $this->initProduitsCepages();

        $degustateurs =  array();

        foreach(CompteTagsView::getInstance()->listByTags('automatique', 'degustateur') as $row) {
            $compte = CompteClient::getInstance()->find($row->id);
            $degustateurs[$compte->nom." ".$compte->prenom] = $compte;
        }

        $degustation = null;
        $ligne=0;
        foreach(file($arguments['csv']) as $line) {
          $ligne++;
          $line = str_replace("\n", "", $line);
          $data = str_getcsv($line,';');
          if (!$data) {
            continue;
          }

          if($data[self::CSV_NUMERO_ECHANTILLON] == 'Factice') {
              continue;
          }

          if(trim($data[self::CSV_OPERATEUR]) == 'Leurre') {
              continue;
          }

          if (!trim($data[self::CSV_RESULTAT_ORGANO]) && !trim($data[self::CSV_RESULTAT_LABO])) {
              continue;
          }

          $degustation_date = $this->formatDate(trim($data[self::CSV_DATE_COMMISSION]));

          if(!$degustation_date) {
              continue;
          }
          $data[self::CSV_ID] = trim($data[self::CSV_ID]);

          if(!isset($this->dates[$data[self::CSV_ID]])) {
              $heure = preg_replace("/-.*$/", "", $data[self::CSV_ID]);
              $minute = preg_replace("/^.*-/", "", $data[self::CSV_ID]);

              if($heure > 23) {
                  $heure = rand(1,23);
              }

              if($minute > 59) {
                  $minute = rand(1,59);
              }
              $this->dates[$data[self::CSV_ID]] = $degustation_date." ".sprintf("%02d:%02d:00", $heure, $minute);
          }
          $date = $this->dates[$data[self::CSV_ID]];

          $heure = preg_replace("/-.*$/", "", $data[self::CSV_ID]);
          $minute = preg_replace("/^.*-/", "", $data[self::CSV_ID]);

          $campagne = trim($data[self::CSV_CAMPAGNE]);

          $newDegustation = new Degustation();

          if($options['region']) {
              $newDegustation->region = $options['region'];
          }

          $newDegustation->numero_archive = sprintf("%05d", preg_replace("/^.*-/", "", $data[self::CSV_ID]));
          $newDegustation->date=$date;
          $newDegustation->lieu = trim($data[self::CSV_LIEU_NOM])." — ".trim($data[self::CSV_LIEU_ADRESSE])." ".trim($data[self::CSV_LIEU_CODE_POSTAL])." ".trim($data[self::CSV_LIEU_COMMUNE]);
          $newDegustation->campagne=$campagne;
          $newDegustation->max_lots = 1;
          $newDegustation->constructId();

          if($degustation && $newDegustation->_id != $degustation->_id) {
              $this->saveDegustation($degustation);
              $degustation = null;
          }
          if (!$degustation) {
              $degustation = acCouchdbManager::getClient()->find($newDegustation->_id);
          }
          if(!$degustation) {
              $degustation = $newDegustation;
          }
          if($data[self::CSV_TYPE_LIGNE] == "JURY") {

              if(!isset($degustateurs[$data[self::CSV_RAISON_SOCIALE]])) {
                  echo "WARNING;Dégustateur non trouvé;".$line."\n";
                  continue;
              }

              if($data[self::CSV_COLLEGE] == "Porteur de mémoire") {
                  $college = "degustateur_porteur_de_memoire";
              }

              if($data[self::CSV_COLLEGE] == "Technicien") {
                  $college = "degustateur_technicien";
              }

              if($data[self::CSV_COLLEGE] == "Usager du produit") {
                  $college = "degustateur_usager_du_produit";
              }

              $degustateur = $degustation->degustateurs->add($college)->add($degustateurs[$data[self::CSV_RAISON_SOCIALE]]->_id);
              $degustateur->add('libelle', $degustateurs[$data[self::CSV_RAISON_SOCIALE]]->nom_a_afficher);
              $degustateur->add('confirmation', true);

              continue;
          }

          $produitKey = $this->clearProduitKey(KeyInflector::slugify(trim($data[self::CSV_PRODUIT])));

          if(!$produitKey) {
              continue;
          }

          if (!isset($this->produits[$produitKey])) {
            echo "WARNING;produit $produitKey non trouvé;pas d'import;$line\n";
            continue;
          }
          $produit = $this->produits[$produitKey];

          if(!$degustation->region) {
              foreach(RegionConfiguration::getInstance()->getOdgRegions() as $region) {
                  if(RegionConfiguration::getInstance()->isHashProduitInRegion($region, $produit->getHash())) {
                    $degustation->add('region', $region);
                  }
              }
          }

          $etablissement = $this->identifyEtablissement(preg_replace("/[ ]*[0-9]+$/", "", $data[self::CSV_OPERATEUR]), preg_replace("/^.* ([0-9]+)$/", '\1', $data[self::CSV_OPERATEUR]));

          if (!$etablissement) {
               echo "WARNING;établissement non trouvé ".$data[self::CSV_OPERATEUR].";pas d'import;$line\n";
               continue;
          }

          $data[self::CSV_MILLESIME] = preg_replace("/^.* ([0-9]+)$/", '\1', trim($data[self::CSV_PRODUIT]));
          $volume = str_replace(',','.',trim($data[self::CSV_VOLUME])) * 1;
          $numeroCuve = trim($data[self::CSV_NUM_LOT_OPERATEUR]);
          $numeroAnonymat = trim($data[self::CSV_NUMERO_ANONYMAT]);
          $numeroTable = trim($data[self::CSV_NUMERO_TABLE]);
          $numeroEchantillon = sprintf("%05d", trim($data[self::CSV_NUMERO_ECHANTILLON]));

          /*$lot = MouvementLotView::getInstance()->find($etablissement->identifiant, array('volume' => $volume, 'numero_logement_operateur' => $numeroCuve, 'produit_hash' => $produit->getHash(), 'millesime' => $data[self::CSV_MILLESIME], 'statut' => Lot::STATUT_NONAFFECTABLE));*/
           $lots = MouvementLotView::getInstance()->find($etablissement->identifiant, array('volume' => $volume, 'produit_hash' => $produit->getHash(), 'millesime' =>  $data[self::CSV_MILLESIME], 'numero_logement_operateur' => $numeroCuve, 'statut' => [Lot::STATUT_AFFECTABLE, Lot::STATUT_MANQUEMENT_EN_ATTENTE]), false);
           $lot = null;
           if(count($lots) == 1) {
              $lot = $lots[0];
           }

           if(!$lot) {
               $lots = MouvementLotView::getInstance()->find($etablissement->identifiant, array('volume' => $volume, 'produit_hash' => $produit->getHash(), 'millesime' => $data[self::CSV_MILLESIME], 'statut' => [Lot::STATUT_AFFECTABLE, Lot::STATUT_MANQUEMENT_EN_ATTENTE]), false);
               if(count($lots) == 1) {
                   $lot = $lots[0];
               }
           }

           if(!$lot) {
               foreach($lots as $key => $lot) {
                   if($numeroCuve && !preg_match("|^".$numeroCuve." |", $lot->numero_logement_operateur)) {
                       unset($lots[$key]);
                   }
               }
               $lots =  array_values($lots);
               if(count($lots) == 1) {
                   $lot = $lots[0];
               }
           }

           if(!$lot && $numeroCuve) {
               $lots = MouvementLotView::getInstance()->find($etablissement->identifiant, array('produit_hash' => $produit->getHash(), 'millesime' => $data[self::CSV_MILLESIME], 'numero_logement_operateur' => $numeroCuve, 'statut' => [Lot::STATUT_AFFECTABLE, Lot::STATUT_MANQUEMENT_EN_ATTENTE]), false);
               if(count($lots) == 1) {
                   $lot = $lots[0];
               }
           }

          if(!$lot) {
              echo "ERROR;mouvement de lot d'origin non trouvé;$line\n";
              continue;
          }

          $date_lot_securite =  date("Y-m-d",strtotime($lot->date." -6 months"));
          if ($date_lot_securite > $date) {
              echo "ERROR: La Date d'un lot (".$lot->date." - $date_lot_securite) ne peut être suppérieure à la date de dégustation ($date);$line\n";
              continue;
          }

          $lot = $degustation->addLot($lot, false);

          $lot->volume = $volume;

          $lot->preleve = preg_replace('/ .*/', '', $date);

          $lot->_set('numero_table', intval($numeroTable));

          if ($lot->numero_table) {
              $lot->numero_anonymat = sprintf("%02d", $numeroAnonymat);
          }
          $lot->email_envoye = $lot->preleve;

          if(trim($data[self::CSV_RESULTAT_LABO]) == "NC") {
             $lot->statut = Lot::STATUT_NONCONFORME;
             $lot->conformite = Lot::CONFORMITE_NONCONFORME_ANALYTIQUE;
           } elseif(trim($data[self::CSV_RESULTAT_ORGANO]) == "NC") {
             $lot->statut = Lot::STATUT_NONCONFORME;
             $lot->conformite = Lot::CONFORMITE_NONCONFORME_ORGANOLEPTIQUE;
          } else {
             $lot->statut = Lot::STATUT_CONFORME;
             $lot->conformite = Lot::CONFORMITE_CONFORME;
          }
        }



        if($degustation) {
            $this->saveDegustation($degustation);
        }
      }

    public function saveDegustation($degustation) {
        if($degustation->date > date('Y-m-d H:i:s')) {
            $degustation->etape = DegustationEtapes::ETAPE_LOTS;
        } else {
            $degustation->etape = DegustationEtapes::ETAPE_VISUALISATION;
            $degustation->add("validation", explode(" ", $degustation->date)[0]);
            $degustation->add("validation_oi", $degustation->validation);
        }
        $degustation->save();
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

}

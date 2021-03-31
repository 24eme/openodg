<?php

class ImportChgtDenomTask extends importOperateurIACsvTask
{
  const CSV_CVI = 0;
  const CSV_NUM_DOSSIER = 1;
  const CSV_NUM_LOT_ODG = 2;
  const CSV_NUM_LOT_OPERATEUR = 3;
  const CSV_RAISON_SOCIALE = 4;
  const CSV_NOM = 4;
  const CSV_ADRESSE_1 = 5;
  const CSV_ADRESSE_2 = 6;
  const CSV_CODE_POSTAL = 7;
  const CSV_VILLE = 8;
  const CSV_DATE_DECLARATION = 9;
  const CSV_IGP_INITIAL = 10;
  const CSV_VOLUME_INITIAL = 11;
  const CSV_VOLUME_CONCERNE = 12;
  const CSV_IGP_FINAL = 13;
  const CSV_COULEUR = 14;
  const CSV_CEPAGE = 15;
  const CSV_TYPE_LOT = 16;

  protected $etablissements;
  protected $produits;
  protected $cepages;

  public static $correspondancesCepages = array(
    "Cabernet sauvignon N" => "CAB-SAUV-N",
    "Chardonnay B" => "CHARDONN.B",
    "Cinsault N" => "CINSAUT N",
    "Clairette B" => "CLAIRET.B",
    "Mourvèdre N" => "MOURVED.N",
    "Muscat à petits grains B" => "MUS.PT.G.B",
    "Muscat à petits grains Rs" => "MUS.P.G.RS",
    "Muscat d'Hambourg N" => "MUS.HAMB.N",
    "Muscat PG B" => "MUS.PT.G.B",
    "Nielluccio N" => "NIELLUC.N",
    "Sauvignon B" => "SAUVIGN.B",
    "Savagnin Blanc B" => "SAVAGN.B",
    "Vermentino B" => "VERMENT.B"
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
        $this->name = 'chgt-denom-ia';
        $this->briefDescription = 'Import des changements de lots (via un csv)';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $this->initProduitsCepages();

        $this->etablissements = EtablissementAllView::getInstance()->getAll();
        $drev = null;
        $ligne = 0;
        foreach(file($arguments['csv']) as $line) {
            $ligne++;
            $line = str_replace("\n", "", $line);
            $data = str_getcsv($line, ';');
            if ( !$data || !isset($data[self::CSV_DATE_DECLARATION]) ) {
              continue;
            }

            if ($data[self::CSV_DATE_DECLARATION]) {
                $dateDeclaration = (preg_match('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/', trim($data[self::CSV_DATE_DECLARATION]), $m))? $m[3].'-'.$m[2].'-'.$m[1] : null;
            }else{
                echo "WARNING: pas de date de Déclaration trouvée : ".$data[self::CSV_DATE_DECLARATION]."\n";
                continue;
            }

            if($dateDeclaration < "2019-01-01") {
                continue;
            }

            $etablissement = $this->identifyEtablissement($data[self::CSV_RAISON_SOCIALE], $data[self::CSV_CVI], $data[self::CSV_CODE_POSTAL]);
            if (!$etablissement) {
               echo "ERROR;établissement non trouvé ".$data[self::CSV_RAISON_SOCIALE].";pas d'import;$line\n";
               continue;
            }
            $produitIntialKey = $this->clearProduitKey(KeyInflector::slugify(trim($data[self::CSV_IGP_INITIAL])." ".trim($data[self::CSV_COULEUR])));
            if (!isset($this->produits[$produitIntialKey])) {
              echo "ERROR;produit non trouvé ".$data[self::CSV_IGP_INITIAL].' '.$data[self::CSV_COULEUR].";pas d'import;$line\n";
              continue;
            }
            $produitInitial = $this->produits[$produitIntialKey];
            $produitFinalKey = $this->clearProduitKey(KeyInflector::slugify(trim($data[self::CSV_IGP_FINAL])." ".trim($data[self::CSV_COULEUR])));
            if (!isset($this->produits[$produitFinalKey])) {
              echo "ERROR;produit non trouvé ".$data[self::CSV_IGP_FINAL].' '.$data[self::CSV_COULEUR].";pas d'import;$line\n";
              continue;
            }
            $produitFinal = $this->produits[$produitFinalKey];

            $volumeInitial = str_replace(',','.',trim($data[self::CSV_VOLUME_INITIAL])) * 1;
            $volumeConcerne = str_replace(',','.',trim($data[self::CSV_VOLUME_CONCERNE])) * 1;

            $numeroDossier = sprintf("%05d", trim($data[self::CSV_NUM_DOSSIER]));
            $numeroArchive = sprintf("%05d", trim($data[self::CSV_NUM_LOT_ODG]));
            $numeroCuve = $data[self::CSV_NUM_LOT_OPERATEUR];

            $mouvementsRealOrigLot = MouvementLotView::getInstance()->getMouvements($etablissement->identifiant,
                     array(
//                            'volume' => $volumeInitial,
                            'produit_hash' => $produitInitial->getHash(),
                            'statut' => Lot::STATUT_CHANGEABLE
                          )
            );
            $mouvementsOrigLot = $mouvementsRealOrigLot;
            $mouvementsLot = array();
            //On exclue de changement antérieure à la revendication
            foreach($mouvementsOrigLot as $mvt) {
                $date = preg_replace('/(\d+)\/(\d+)\/(\d+)/', '$3-$2-$1', $data[self::CSV_DATE_DECLARATION]);
                $date_lot_securite =  date("Y-m-d",strtotime($mvt->date." -6 months"));
                if ($date < $date_lot_securite) {
                    continue;
                }
                $mouvementsLot[] = $mvt;
            }
            $mouvementsOrigLot = $mouvementsLot;
            //On excluse les cépages non identiques
            if (count($mouvementsLot) > 1) {
                $mouvementsLot = array();
                foreach($mouvementsOrigLot as $mvt) {
                    if (!$data[self::CSV_CEPAGE]) {
                        $pas_de_cepage = true;
                        foreach($mvt->cepages as $cepage => $volume) {
                            $pas_de_cepage = false;
                            continue;
                        }
                        if ($pas_de_cepage) {
                            $mouvementsLot[] = $mvt;
                        }
                    }else {
                        foreach($mvt->cepages as $cepage => $volume) {
                            if (strtolower($cepage)  == strtolower($data[self::CSV_CEPAGE])) {
                                $mouvementsLot[] = $mvt;
                                break 1;
                            }
                        }
                    }
                }
                if (count($mouvementsLot) > 1) {
                    $mouvementsOrigLot = $mouvementsLot;
                }
            }
            if (!count($mouvementsLot)) {
                echo "Warning: Pas de mouvement trouvé (cépage & date antérieures exclus)\n";
                echo "\t".implode(', ', $data)."\n";
                continue;
            }
            //Comparaison des volumes
            if (count($mouvementsLot) > 1) {
                $mouvementsLot = array();
                foreach($mouvementsOrigLot as $mvt) {
                    if ($volumeInitial == $mvt->volume) {
                        $mouvementsLot[] = $mvt;
                    }
                }
            }
            //Logements opérateurs
            if (count($mouvementsLot) != 1) {
                $mouvementsLot = array();
                foreach($mouvementsOrigLot as $mvt) {
                    if ($this->clearLogement($mvt->numero_logement_operateur) == $this->clearLogement($data[self::CSV_NUM_LOT_OPERATEUR])) {
                        $mouvementsLot[] = $mvt;
                    }
                }
            }
            //Comparaison volume ET logement
            if (count($mouvementsLot) != 1) {
                $mouvementsLot = array();
                foreach($mouvementsOrigLot as $mvt) {
                    if (($volumeInitial == $mvt->volume) && ($this->clearLogement($mvt->numero_logement_operateur) == $this->clearLogement($data[self::CSV_NUM_LOT_OPERATEUR]))) {
                        $mouvementsLot[] = $mvt;
                    }
                }
            }

            if ((count($mouvementsLot) > 1) || !count($mouvementsLot)) {
                echo "WARNING: Pas de mouvements trouvés\n";
                echo "\t$line\n";
/*
                echo "========================\n";
                echo "Résultat : \n";
                print_r($mouvementsRealOrigLot);
                if (count($mouvementsLot) > 1) {
                    echo "======= Trop  Trouvé ======\n";
                }else{
                    echo "======== Pas Trouvé =======\n";
                }
                */
                continue;
            }
            $mouvementLot = $mouvementsLot[0];
            if(!$mouvementLot) {
                echo "ERROR;mouvement de lot d'origin non trouvé;$line\n";
                continue;
            }

            $chgtDenom = ChgtDenomClient::getInstance()->createDoc($etablissement->identifiant, $dateDeclaration." ".sprintf("%02d", rand(0,23)).":".sprintf("%02d", rand(0,59)).":".sprintf("%02d", rand(0,59)), true);
            $chgtDenom->constructId();
            $chgtDenom->setLotOrigine($mouvementLot);
            $chgtDenom->changement_produit_hash = $produitFinal->getHash();
            $chgtDenom->changement_volume = $volumeConcerne;
            $chgtDenom->generateLots();
            if (!$chgtDenom->isTotal()) {
                $chgtDenom->lots[1]->numero_dossier = $numeroDossier;
                $chgtDenom->lots[1]->numero_archive = $numeroArchive;
                $chgtDenom->lots[1]->affectable = true;
                $chgtDenom->lots[0]->affectable = false;
            } elseif($chgtDenom->isTotal()) {
                $chgtDenom->lots[0]->numero_dossier = $numeroDossier;
                $chgtDenom->lots[0]->numero_archive = $numeroArchive;
                $chgtDenom->lots[0]->affectable = true;
            }

            $chgtDenom->validate($dateDeclaration);
            $chgtDenom->validateOdg($dateDeclaration);
            try {
                $chgtDenom->save();
            } catch (Exception $e) {
                echo "ERROR;".$chgtDenom->_id.";".$e->getMessage()."\n";
            }
        }
    }

    protected function clearLogement($s) {
        return preg_replace('/[^0-9a-z]/', '', strtolower($s));
    }

    public function initProduitsCepages() {
      $this->produits = array();
      $this->cepages = array();
      $produits = ConfigurationClient::getInstance()->getConfiguration()->declaration->getProduits();
      foreach ($produits as $key => $produit) {
        $this->produits[KeyInflector::slugify($produit->getLibelleFormat())] = $produit;
        foreach($produit->getCepagesAutorises() as $ca) {
          $this->cepages[KeyInflector::slugify($ca)] = $ca;
        }
      }
    }
}

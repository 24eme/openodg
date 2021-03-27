<?php

class ImportChgtDenomTask extends sfBaseTask
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
            if (!$data) {
              continue;
            }

            $dateDeclaration = (preg_match('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/', trim($data[self::CSV_DATE_DECLARATION]), $m))? $m[3].'-'.$m[2].'-'.$m[1] : null;

            if($dateDeclaration < "2019-01-01") {
                continue;
            }

            $etablissement = $this->identifyEtablissement($data);
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

            $mouvementLot = MouvementLotView::getInstance()->find($etablissement->identifiant, array('volume' => $volumeInitial, 'numero_logement_operateur' => $numeroCuve, 'produit_hash' => $produitInitial->getHash(), 'statut' => Lot::STATUT_AFFECTABLE));

            if(!$mouvementLot) {
                echo "ERROR;mouvement de lot d'origin non trouvé;$line\n";
                continue;
            }

            $chgtDenom = ChgtDenomClient::getInstance()->createDoc($etablissement->identifiant, $dateDeclaration." ".sprintf("%02d", rand(0,23)).":".sprintf("%02d", rand(0,59)).":".sprintf("%02d", rand(0,59)), true);
            $chgtDenom->constructId();
            $chgtDenom->setChangementType(ChgtDenomClient::CHANGEMENT_TYPE_CHANGEMENT);
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

    protected function clearProduitKey($key) {
      $key = str_replace('PAYS-DES-', '', $key);
      $key = str_replace('VAR-VAR-', 'VAR-', $key);
      $key = str_replace('IGP-BDR-', 'BOUCHES-DU-RHONE-', $key);
      return $key;
    }

    protected function identifyEtablissement($data) {
        $key = KeyInflector::slugify(str_replace(" ", "", $data[self::CSV_CVI].$data[self::CSV_RAISON_SOCIALE].$data[self::CSV_NOM]));

        if(isset($this->etablissementsCache[$key])) {
            return $this->etablissementsCache[$key];
        }
        foreach ($this->etablissements as $etab) {
            if (isset($data[self::CSV_CVI]) && trim($data[self::CSV_CVI]) && $etab->key[EtablissementAllView::KEY_CVI] == trim($data[self::CSV_CVI])) {
                $this->etablissementsCache[$key] = EtablissementClient::getInstance()->find($etab->id);

                return $this->etablissementsCache[$key];
            }
            if (isset($data[self::CSV_RAISON_SOCIALE]) && trim($data[self::CSV_RAISON_SOCIALE]) && KeyInflector::slugify($etab->key[EtablissementAllView::KEY_NOM]) == KeyInflector::slugify(trim($data[self::CSV_RAISON_SOCIALE]))) {
                $this->etablissementsCache[$key] = EtablissementClient::getInstance()->find($etab->id);

                return $this->etablissementsCache[$key];
            }
            if (isset($data[self::CSV_RAISON_SOCIALE]) && trim($data[self::CSV_RAISON_SOCIALE]) && KeyInflector::slugify($etab->value[EtablissementAllView::VALUE_RAISON_SOCIALE]) == KeyInflector::slugify(trim($data[self::CSV_RAISON_SOCIALE]))) {
                $this->etablissementsCache[$key] = EtablissementClient::getInstance()->find($etab->id);

                return $this->etablissementsCache[$key];
            }
            if (isset($data[self::CSV_NOM]) && trim($data[self::CSV_NOM]) && KeyInflector::slugify($etab->key[EtablissementAllView::KEY_NOM]) == KeyInflector::slugify(trim($data[self::CSV_NOM]))) {
                $this->etablissementsCache[$key] = EtablissementClient::getInstance()->find($etab->id);

                return $this->etablissementsCache[$key];
            }
            if (isset($data[self::CSV_NOM]) && trim($data[self::CSV_NOM]) && KeyInflector::slugify($etab->value[EtablissementAllView::VALUE_RAISON_SOCIALE]) == KeyInflector::slugify(trim($data[self::CSV_NOM]))) {
                $this->etablissementsCache[$key] = EtablissementClient::getInstance()->find($etab->id);

                return $this->etablissementsCache[$key];
            }
        }
        return null;
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

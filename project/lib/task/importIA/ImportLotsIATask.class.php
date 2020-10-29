<?php

class ImportLotsIATask extends sfBaseTask
{
  const CSV_NUM_DOSSIER = 0;
  const CSV_NUM_LOT_ODG = 1;
  const CSV_CVI = 2;
  const CSV_RAISON_SOCIALE = 3;
  const CSV_NOM = 4;
  const CSV_ADRESSE_1 = 5;
  const CSV_ADRESSE_2 = 6;
  const CSV_CODE_POSTAL = 7;
  const CSV_VILLE = 8;
  const CSV_FAX = 9;
  const CSV_TELEPHONE = 10;
  const CSV_FAMILLE = 11;
  const CSV_NUM_LOT_OPERATEUR = 12;
  const CSV_DESTINATION_TYPE = 13;
  const CSV_APPELLATION = 14;
  const CSV_COULEUR = 15;
  const CSV_CEPAGE_1 = 16;
  const CSV_POURCENT_CEPAGE_1 = 17;
  const CSV_CEPAGE_2 = 18;
  const CSV_POURCENT_CEPAGE_2 = 19;
  const CSV_CEPAGE_3 = 20;
  const CSV_POURCENT_CEPAGE_3 = 21;
  const CSV_MILLESIME = 22;
  const CSV_CAMPAGNE = 23;
  const CSV_VOLUME_RESIDUEL = 24;
  const CSV_VOLUME_INITIAL = 25;
  const CSV_DESTINATION = 26;
  const CSV_TRANSACTION_DATE = 27;
  const CSV_CONF = 28;
  const CSV_PRELEVE = 29;
  const CSV_STATUT = 30;
  const CSV_DATE_COMMISSION = 31;
  const CSV_DATE_VALIDATION = 32;
  const CSV_NOM_SITE = 33;
  const CSV_ADRESSE_1_SITE = 34;
  const CSV_ADRESSE_2_SITE = 35;
  const CSV_CODE_POSTAL_SITE = 36;
  const CSV_VILLE_SITE = 37;
  const CSV_EMAIL = 38;

  protected $date;
  protected $convert_statut;
  protected $convert_activites;
  protected $etablissements;
  protected $produits;
  protected $cepages;

  public static $types = array('B' => DRevClient::LOT_DESTINATION_CONDITIONNEMENT, 'VF' => DRevClient::LOT_DESTINATION_VRAC_FRANCE, 'VHF' => DRevClient::LOT_DESTINATION_VRAC_EXPORT);

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
        $this->name = 'lots-ia';
        $this->briefDescription = 'Import des lots (via un csv)';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $this->initProduitsCepages();

        $this->etablissements = EtablissementAllView::getInstance()->getAll();
        $ligne = 0;
        foreach(file($arguments['csv']) as $line) {
            $ligne++;
            $line = str_replace("\n", "", $line);
            $data = str_getcsv($line, ';');
            if (!$data) {
              continue;
            }
            $etablissement = $this->identifyEtablissement($data);
            if (!$etablissement) {
               echo "WARNING;établissement non trouvé ".$data[self::CSV_RAISON_SOCIALE].";pas d'import;$line\n";
               continue;
            }
            $produitKey = KeyInflector::slugify(trim($data[self::CSV_APPELLATION])." ".trim($data[self::CSV_COULEUR]));
            if (!isset($this->produits[$produitKey])) {
              echo "WARNING;produit non trouvé ".$data[self::CSV_APPELLATION].' '.$data[self::CSV_COULEUR].";pas d'import;$line\n";
              continue;
            }
            $produit = $this->produits[$produitKey];
            $cepages = array();
            $volume = str_replace(',','.',trim($data[self::CSV_VOLUME_INITIAL])) * 1;
            if (trim($data[self::CSV_CEPAGE_1])) {
              if (!isset($this->cepages[KeyInflector::slugify(trim($data[self::CSV_CEPAGE_1]))])) {
                echo "WARNING;cepage_1 non trouvé ".$data[self::CSV_CEPAGE_1].";pas d'import;$line\n";
                continue;
              }
              $pourcentage = trim($data[self::CSV_POURCENT_CEPAGE_1]) * 1;
              $pourcentage = ($pourcentage > 1)? round($pourcentage/100, 2) : $pourcentage;
              $cepages[$this->cepages[KeyInflector::slugify(trim($data[self::CSV_CEPAGE_1]))]] = ($pourcentage > 0)? round($volume * $pourcentage, 2) : $volume;
            }
            if (trim($data[self::CSV_CEPAGE_2])) {
              if (!isset($this->cepages[KeyInflector::slugify(trim($data[self::CSV_CEPAGE_2]))])) {
                echo "WARNING;cepage_2 non trouvé ".$data[self::CSV_CEPAGE_2].";pas d'import;$line\n";
                continue;
              }
              $pourcentage = trim($data[self::CSV_POURCENT_CEPAGE_2]) * 1;
              $pourcentage = ($pourcentage > 1)? round($pourcentage/100, 2) : $pourcentage;
              $cepages[$this->cepages[KeyInflector::slugify(trim($data[self::CSV_CEPAGE_2]))]] = ($pourcentage > 0)? round($volume * $pourcentage, 2) : $volume;
            }
            if (trim($data[self::CSV_CEPAGE_3])) {
              if (!isset($this->cepages[KeyInflector::slugify(trim($data[self::CSV_CEPAGE_3]))])) {
                echo "WARNING;cepage_3 non trouvé ".$data[self::CSV_CEPAGE_3].";pas d'import;$line\n";
                continue;
              }
              $pourcentage = trim($data[self::CSV_POURCENT_CEPAGE_3]) * 1;
              $pourcentage = ($pourcentage > 1)? round($pourcentage/100, 2) : $pourcentage;
              $cepages[$this->cepages[KeyInflector::slugify(trim($data[self::CSV_CEPAGE_3]))]] = ($pourcentage > 0)? round($volume * $pourcentage, 2) : $volume;
            }
            $campagne = preg_replace('/\/.*/', '', trim($data[self::CSV_CAMPAGNE]));
            $millesime = preg_match('/^[0-9]{4}$/', trim($data[self::CSV_MILLESIME]))? trim($data[self::CSV_MILLESIME])*1 : $campagne;
            $numero = trim($data[self::CSV_NUM_LOT_ODG]).' | '.trim($data[self::CSV_NUM_LOT_OPERATEUR]);
            $destinationDate = (preg_match('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/', trim($data[self::CSV_TRANSACTION_DATE]), $m))? $m[3].'-'.$m[2].'-'.$m[1] : null;
            $types = self::$types;
            $destination = (isset($types[trim($data[self::CSV_DESTINATION_TYPE])]))? $types[trim($data[self::CSV_DESTINATION_TYPE])] : null;
            $date = (preg_match('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/', trim($data[self::CSV_DATE_VALIDATION]), $m))? $m[3].'-'.$m[2].'-'.$m[1] : null;

            $drev = DRevClient::getInstance()->findMasterByIdentifiantAndCampagne($etablissement->identifiant, $campagne);

            if (!$drev) {
              $drev = DRevClient::getInstance()->createDoc($etablissement->identifiant, $campagne);
              $drev->constructId();
              $drev->storeDeclarant();
            }

            $lot = $drev->getOrAdd('lots')->add();

            $lot->produit_hash = $produit->getHash();
            $lot->produit_libelle = $produit->getLibelleFormat();
            $lot->cepages = $cepages;
            $lot->elevage = 0;
            $lot->id_document = $drev->_id;
            $lot->millesime = $millesime;
            $lot->numero = $numero;
            $lot->volume = $volume;
            $lot->destination_type = $destination;
            $lot->destination_date = $destinationDate;
            $lot->date = $date;

            $nbOc = 0;
            foreach($drev->lots as $l) {
              if ($lot->getUnicityKey() == $l->getUnicityKey()) {
                $nbOc++;
              }
            }

            if ($nbOc > 1) {
              $lot->delete();
            }

            $drev->save();
        }
    }

    protected function identifyEtablissement($data) {
        foreach ($this->etablissements as $etab) {
            if (isset($data[self::CSV_CVI]) && trim($data[self::CSV_CVI]) && $etab->key[EtablissementAllView::KEY_CVI] == trim($data[self::CSV_CVI])) {
                return EtablissementClient::getInstance()->find($etab->id);
                break;
            }
            if (isset($data[self::CSV_RAISON_SOCIALE]) && trim($data[self::CSV_RAISON_SOCIALE]) && KeyInflector::slugify($etab->key[EtablissementAllView::KEY_NOM]) == KeyInflector::slugify(trim($data[self::CSV_RAISON_SOCIALE]))) {
                return EtablissementClient::getInstance()->find($etab->id);
                break;
            }
            if (isset($data[self::CSV_RAISON_SOCIALE]) && trim($data[self::CSV_RAISON_SOCIALE]) && KeyInflector::slugify($etab->value[EtablissementAllView::VALUE_RAISON_SOCIALE]) == KeyInflector::slugify(trim($data[self::CSV_RAISON_SOCIALE]))) {
                return EtablissementClient::getInstance()->find($etab->id);
                break;
            }
            if (isset($data[self::CSV_NOM]) && trim($data[self::CSV_NOM]) && KeyInflector::slugify($etab->key[EtablissementAllView::KEY_NOM]) == KeyInflector::slugify(trim($data[self::CSV_NOM]))) {
                return EtablissementClient::getInstance()->find($etab->id);
                break;
            }
            if (isset($data[self::CSV_NOM]) && trim($data[self::CSV_NOM]) && KeyInflector::slugify($etab->value[EtablissementAllView::VALUE_RAISON_SOCIALE]) == KeyInflector::slugify(trim($data[self::CSV_NOM]))) {
                return EtablissementClient::getInstance()->find($etab->id);
                break;
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

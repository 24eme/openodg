<?php

class ImportDegustationIATask extends sfBaseTask
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
  const CSV_TYPE = 13;
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

  const TYPE_REVENDIQUE = 'R';

  const STATUT_PRELEVE = "PRELEVE";
  const STATUT_PRELEVABLE = "PRELEVE";
  const STATUT_DEGUSTE = "DEGUSTE";
  const STATUT_CONFORME = "CONFORME";
  const STATUT_NONCONFORME = "NON_CONFORME";
  const STATUT_CHANGE = "CHANGE";
  const STATUT_DECLASSE = "DECLASSE";

  protected $date;
  protected $convert_statut;
  protected $convert_activites;
  protected $etablissements;
  protected $produits;
  protected $cepages;

  public static $types = array('B' => DRevClient::LOT_DESTINATION_CONDITIONNEMENT, 'VF' => DRevClient::LOT_DESTINATION_VRAC_FRANCE, 'VHF' => DRevClient::LOT_DESTINATION_VRAC_EXPORT);
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
    public static $correspondancesStatuts = array(
      "Conforme" => self::STATUT_CONFORME,
      "Déclassé" => self::STATUT_DECLASSE,
      "Non Conforme" => self::STATUT_NONCONFORME,
      "Prélevé A" => self::STATUT_PRELEVE,
      "Prélevé NA" => self::STATUT_PRELEVE,
      "Prévu" => self::STATUT_PRELEVE,
      "Revendiqué C" => array(self::STATUT_PRELEVABLE, self::STATUT_PRELEVE),
      "Revendiqué NC" => array(self::STATUT_PRELEVABLE, self::STATUT_PRELEVE)
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

    protected function clearProduitKey($key) {
      $key = str_replace('PAYS-DES-', '', $key);
      return $key;
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

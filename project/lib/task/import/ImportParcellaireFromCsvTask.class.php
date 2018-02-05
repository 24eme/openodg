<?php

class ImportParcellaireFromCsvTask extends sfBaseTask
{

    protected $file_path = null;
    protected $configuration = null;
    protected $configurationProduits = array();

    const CSV_ID_SECTION = 0;
    const CSV_CODE_COMMUNE_RECH = 1;
    const CSV_REF_CADASTRALE = 2;          /* /!\ */
    const CSV_CONTENANCE_CADASTRALE = 3;   /* /!\ */
    const CSV_CODE_DEPARTEMENT = 4;
    const CSV_CODE_INSEE_COMMUNE = 5;
    const CSV_CODE_COMMUNE = 6;
    const CSV_LIBELLE_COMMUNE = 7;
    const CSV_LIEUDIT_COMMUNE = 8;
    const CSV_CIVILITE_PROPRIETAIRE = 9;
    const CSV_NOM_PROPRIETAIRE = 10;
    const CSV_PRENOM_PROPRIETAIRE = 11;
    const CSV_DATE_DEBUT_VALIDITE = 12;
    const CSV_NUMERO_ORDRE = 13;
    const CSV_DATE_MODIFICATION = 14;
    const CSV_EVV = 15;                   /* /!\ */
    const CSV_LIBELLE_EVV = 16;
    const CSV_ETAT_METIER = 17;

    const CSV_ETAT_SEGMENT = 18;
    const CSV_CODE_AIRE = 19;
    const CSV_ICAIR = 20;              /* /!\ */
    const CSV_LIBELLE_AIRE = 21;     /* /!\ */
    const CSV_CODE_PRODUIT = 22;  /* /!\ */
    const CSV_LIBELLE_PRODUIT = 23;  /* /!\ */
    const CSV_CODE_CEPAGE = 24;
    const CSV_LIBELLE_CEPAGE = 25;

    const CSV_CODE_COULEUR = 26;
    const CSV_CODE_PORTEGREFFE = 27;
    const CSV_LIBELLE_PORTEGREFFE = 28;
    const CSV_LIBELLE_MOTIF_ENCEPAGEMENT = 29; /* /!\ COMMENTAIRE */
    const CSV_LIBELLE_MODE_SAVOIRFAIRE = 30;
    const CSV_LIBELLE_DATE_SAVOIRFAIRE = 31;
    const CSV_CAMPAGNE_PLANTATION = 32;

    const CSV_SUPERFICIE = 33;        /* /!\ */
    const CSV_ECART_RANG = 34;
    const CSV_ECART_PIED = 35;
    const CSV_DATE_DEBUT_GESTION = 36;
    const CSV_DATE_FIN_GESTION = 37;


    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "Donnees au format CSV")
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            new sfCommandOption('cremant', null, sfCommandOption::PARAMETER_OPTIONAL, 'Cremant', '0'),
        ));

        $this->namespace = 'import';
        $this->name = 'parcellaire-from-csv';
        $this->briefDescription = "Importe le parcellaire depuis le CSV d'une annee";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {

        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $this->file_path = $arguments['csv'];

        if(!$this->file_path){
          throw new  sfException("Le paramètre du fichier csv doit être renseigné");

        }
        error_reporting(E_ERROR | E_PARSE);
        $this->configuration = ConfigurationClient::getInstance()->getConfiguration();
        $this->configurationProduits = $this->configuration->getProduits();

        foreach(file($this->file_path) as $line) {
            $line = str_replace("\n", "", $line);
            if(preg_match("/^\"ISection\";/", $line)) {
                continue;
            }
            $this->importLineParcellaire($line);
          }
    }

    protected function importLineParcellaire($line){
            $data = str_getcsv($line, ';');
            $cvi = $data[self::CSV_EVV];
            if(!$cvi){
              throw new sfException("le cvi n'existe pas pour la ligne ".implode(',',$line));
            }

            $etablissement = EtablissementClient::getInstance()->findByCvi($cvi);
            if(!$etablissement){
              throw new sfException("l'établissement de cvi ".$cvi." n'existe pas dans la base");
            }

            $ref_cadastrale = $data[self::CSV_REF_CADASTRALE];


            $parcellaire = ParcellaireClient::getInstance()->findOrCreateFromEtablissement($etablissement,"2018");

            $parcellaire->addAcheteur(ParcellaireClient::DESTINATION_SUR_PLACE,$etablissement->getIdentifiant());

            foreach ($this->configurationProduits as $key => $p) {
              if($p->getCodeDouane() != trim($data[self::CSV_CODE_PRODUIT])){
                continue;
              }
              $produitParcellaire = $parcellaire->addProduit($p->getHash());
              $produitParcellaire->libelle = $p->getLibelleComplet();

              $m = array();
              if(!preg_match('/([0-9]+)(\ +)([A-Za-z0-9]+)/',$ref_cadastrale,$m)){
                throw new sfException("le format de la référence cadastrale de la ligne ".implode(',',$line)." n'est pas correcte");
              }
              if(count($m) < 4){
                throw new sfException("le format de la référence cadastrale de la ligne ".implode(',',$line)." n'est pas correcte");
              }

              $cepagesAutorisesConf = $p->getCepagesAutorises()->toArray(0,1);
              if(!in_array(trim($data[self::CSV_LIBELLE_CEPAGE]),$cepagesAutorisesConf)){
                throw new sfException("le cepage ".trim($data[self::CSV_LIBELLE_CEPAGE])." ne fait pas parti des cépages autorisés");
              }
              $cepage = trim($data[self::CSV_LIBELLE_CEPAGE]);

              $commune = trim($data[self::CSV_LIBELLE_COMMUNE]);
              $section = trim($m[1]);
              $numero_parcelle = trim($m[3]);
              $dpt = trim($data[self::CSV_CODE_DEPARTEMENT]);

              $campagnePlantation = trim($data[self::CSV_CAMPAGNE_PLANTATION]);

              $key = KeyInflector::slugify($cepage.'-'.$commune . '-' . $section . '-' . $numero_parcelle);
              if($produitParcellaire->detail->exist($key)){
                  if($produitParcellaire->detail->get($key)->exist('campagne_plantation')
                  && substr($produitParcellaire->detail->get($key)->get('campagne_plantation'),0,4) > substr($campagnePlantation,0,4)){
                  echo "Cette parcelle $section $numero_parcelle est plus ancienne que celle importée\n";
                  continue;
                }
              }
              $parcelle = $produitParcellaire->addParcelle($cepage, $commune, $section , $numero_parcelle, $dpt);
              $superficie = floatval(str_replace(',','.',trim($data[self::CSV_SUPERFICIE])));
              $parcelle->superficie = $superficie;
              $parcelle->code_postal = str_replace("'",'',trim($data[self::CSV_CODE_COMMUNE_RECH]));
              $parcelle->cepage = $cepage;

              $parcelle->add('code_insee',trim($data[self::CSV_CODE_INSEE_COMMUNE]));
              $parcelle->add('ecart_rang',trim($data[self::CSV_ECART_RANG]));
              $parcelle->add('ecart_pieds',trim($data[self::CSV_ECART_PIED]));
              $parcelle->add('campagne_plantation',trim($data[self::CSV_CAMPAGNE_PLANTATION]));
              $parcelle->active = false;

              $date2018 = "20180101";
              $dateDebut =  new DateTime(trim($data[self::CSV_DATE_DEBUT_GESTION]));
              $dateFin =  new DateTime(trim($data[self::CSV_DATE_FIN_GESTION]));
              if(($dateDebut->format('Ymd') <= $date2018) && ($date2018 <= $dateFin)){
                $parcelle->active = true;
              }
              echo "Import de la parcelle $section $numero_parcelle !\n";
            }
            $parcellaire->save();
      }
}

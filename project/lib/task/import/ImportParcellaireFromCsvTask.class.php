<?php

class ImportParcellaireFromCsvTask extends sfBaseTask
{

    protected $file_path = null;
    protected $configuration = null;
    protected $configurationProduits = array();
    protected $modes_savoirfaire = array();

    const CSV_ID_SECTION = 0;
    const CSV_INUMPCV = 1; /* ???? correspond à .... */
    const CSV_CODE_COMMUNE_RECH = 2;
    const CSV_REF_CADASTRALE = 3;          /* /!\ */
    const CSV_CONTENANCE_CADASTRALE = 4;   /* /!\ */
    const CSV_CODE_DEPARTEMENT = 5;
    const CSV_CODE_INSEE_COMMUNE = 6;
    const CSV_CODE_COMMUNE = 7;
    const CSV_LIBELLE_COMMUNE = 8;
    const CSV_LIEUDIT_COMMUNE = 9;
    const CSV_CIVILITE_PROPRIETAIRE = 10;
    const CSV_NOM_PROPRIETAIRE = 11;
    const CSV_PRENOM_PROPRIETAIRE = 12;
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
    const CSV_IDU = 38;


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
        $this->modes_savoirfaire = array_flip(ParcellaireClient::$modes_savoirfaire);

        $parcellaire = null;
        foreach(file($this->file_path) as $line) {
            $line = str_replace("\n", "", $line);
            if(preg_match("/^\"ISection\";/", $line)) {
                continue;
            }

            $data = str_getcsv($line, ';');
            $cvi = $data[self::CSV_EVV];
            if(!$cvi){
              throw new sfException("le cvi n'existe pas pour la ligne ".implode(',',$line));
            }

            if(!$parcellaire || $parcellaire->declarant->cvi != $cvi) {
                if($parcellaire) {
                    $this->saveParcellaire($parcellaire);
                    $parcellaire = null;
                }

                $etablissement = EtablissementClient::getInstance()->findByCvi($cvi);
                if(!$etablissement){
                  echo "L'établissement de cvi ".$cvi." n'existe pas dans la base\n";

                  continue;
                }
                $parcellaire = ParcellaireClient::getInstance()->findOrCreate($etablissement->identifiant, "2018-01-01", "INAO");
            }

            $this->importLineParcellaire($line, $parcellaire);
          }

          if($parcellaire) {
              $this->saveParcellaire($parcellaire);
              $parcellaire = null;
          }
    }

    protected function importLineParcellaire($line, $parcellaire){
            $data = str_getcsv($line, ';');

            $ref_cadastrale = $data[self::CSV_REF_CADASTRALE];

            foreach ($this->configurationProduits as $key => $p) {
              if($p->getCodeDouane() != trim($data[self::CSV_CODE_PRODUIT])){
                continue;
              }
              $produitParcellaire = $parcellaire->addProduit($p->getHash());
              $produitParcellaire->libelle = $p->getLibelleComplet();

              $m = array();
              if(!preg_match('/([0-9]+)(\ +)([A-Za-z0-9]+)/',$ref_cadastrale,$m)){
                throw new sfException("le format de la référence cadastrale de la ligne ".$line." n'est pas correcte");
              }
              if(count($m) < 4){
                throw new sfException("le format de la référence cadastrale de la ligne ".$line." n'est pas correcte");
              }

              $cepagesAutorisesConf = $p->getCepagesAutorises()->toArray(0,1);
              if(!in_array(trim($data[self::CSV_LIBELLE_CEPAGE]),$cepagesAutorisesConf)){
                throw new sfException("le cepage ".trim($data[self::CSV_LIBELLE_CEPAGE])." ne fait pas parti des cépages autorisés");
              }
              $cepage = trim($data[self::CSV_LIBELLE_CEPAGE]);

              $commune = trim($data[self::CSV_LIBELLE_COMMUNE]);
              $lieuDit = (trim($data[self::CSV_LIEUDIT_COMMUNE]))? trim($data[self::CSV_LIEUDIT_COMMUNE]) : null;

              $section = trim($data[self::CSV_ID_SECTION]);
              $numero_parcelle = preg_replace('/^[0]+/', '', trim($data[self::CSV_INUMPCV]));

              $campagnePlantation = trim($data[self::CSV_CAMPAGNE_PLANTATION]);

              $key = KeyInflector::slugify($cepage.'-'.$commune . '-' . $section . '-' . $numero_parcelle);
              if($produitParcellaire->detail->exist($key)){
                  if($produitParcellaire->detail->get($key)->exist('campagne_plantation')
                  && substr($produitParcellaire->detail->get($key)->get('campagne_plantation'),0,4) > substr($campagnePlantation,0,4)){
                  echo "Cette parcelle $section $numero_parcelle est plus ancienne que celle importée\n";
                  continue;
                }
              }
              $parcelle = $produitParcellaire->addParcelle($cepage, $campagnePlantation, $commune, $section, $numero_parcelle, $lieuDit);
              $parcelle->superficie = floatval(str_replace(',','.',trim($data[self::CSV_SUPERFICIE])));
              $parcelle->superficie_cadastrale = floatval(str_replace(',','.',trim($data[self::CSV_CONTENANCE_CADASTRALE])));
              $parcelle->code_commune = str_replace("'",'',trim($data[self::CSV_CODE_COMMUNE_RECH]));
              $parcelle->cepage = $cepage;

              $parcelle->add('ecart_rang', trim($data[self::CSV_ECART_RANG]) * 1);
              $parcelle->add('ecart_pieds', trim($data[self::CSV_ECART_PIED]) * 1);

              $date2018 = "20180101";
              if(!preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}/',trim($data[self::CSV_DATE_DEBUT_GESTION]))){
                echo "La date de début de gestion  de la ligne $ligne est mal formattée\n";
                return;
              }
              $dateFinGestionCsv = (trim($data[self::CSV_DATE_FIN_GESTION]))? trim($data[self::CSV_DATE_FIN_GESTION]) : null;
              if($dateFinGestionCsv && !preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}/',$dateFinGestionCsv)){
                echo "La date de fin de gestion  de la ligne $ligne est mal formattée\n";
                return;
              }

              $dateDebut =  new DateTime(trim($data[self::CSV_DATE_DEBUT_GESTION]));
              if($dateFinGestionCsv){
                $dateFin =  new DateTime($dateFinGestionCsv);
              }
              if($dateDebut->format('Ymd') > $date2018){
                $parcelle->active = false;
              }
              if($dateFin && ($date2018 > $dateFin)){
                $parcelle->active = false;
              }
              $mode_savoirfaire = null;
              if(array_key_exists(trim($data[self::CSV_LIBELLE_MODE_SAVOIRFAIRE]),$this->modes_savoirfaire)){

                $mode_savoirfaire = $this->modes_savoirfaire[trim($data[self::CSV_LIBELLE_MODE_SAVOIRFAIRE])];
              }
              if($mode_savoirfaire){
                $parcelle->add('mode_savoirfaire',$mode_savoirfaire);
              }
              if(trim($data[self::CSV_CODE_PORTEGREFFE])){
                $parcelle->add('porte_greffe', trim($data[self::CSV_CODE_PORTEGREFFE]));
              }

              if($parcelle->idu != $data[self::CSV_IDU]) {
                  echo "Le code IDU ". $parcelle->idu."/".$data[self::CSV_IDU]." a été mal formaté (ligne $ligne)\n";
              }

              echo "Import de la parcelle $section $numero_parcelle pour $etablissement->_id !\n";
            }
    }

    protected function saveParcellaire($parcellaire) {
        $parcellaire->save();
        echo "Parcellaire $parcellaire->_id sauvegardé\n";
    }
}

<?php

class importEntitesFromCSVTask extends sfBaseTask
{

    protected $file_path = null;
    protected $chaisAttributsInImport = array();
    protected $isSuspendu = false;


    const CSV_IDENTIFIANT_LIGNE = 0; // used
    const CSV_EVV = 1; // used
    const CSV_SIRET = 2; // used
    const CSV_FORME_SOCIETE = 3; // used
    const CSV_RAISONSOCIALE = 4; // used
    const CSV_CATEGORIE = 5;
    const CSV_LIBELLE = 6;
    const CSV_PPM = 7;
    const CSV_ADRESSE_1 = 8; // used
    const CSV_ADRESSE_2 = 9; // used
    const CSV_CP = 10; // used
    const CSV_VILLE = 11; // used
    const CSV_TELEPHONE_1 = 12; // used
    const CSV_TELEPHONE_2 = 13; // used
    const CSV_EMAIL = 14;
    const CSV_DATE_CREATION = 15; // ATTENTION aux valeurs "00/00/00" used
    const CSV_RIB = 16; //Tout le temps vide !!! inutile
    const CSV_DOMICILIATION = 17; //Tout le temps vide !!! inutile
    const CSV_DATE_MAJ = 18;
    const CSV_USER_MAJ = 19;
    const CSV_OBSERVATION = 20;


    //En faire des contacts
    const CSV_CONTACT_NOM = 21;
    const CSV_CONTACT_PRENOM = 22;
    const CSV_CONTACT_FONCTION = 23;
    const CSV_CONTACT_TELEPHONE = 24;

    const CSV_PRODUCTEUR = 25;
    const CSV_VINIFICATEUR = 26;
    const CSV_CONDITIONNEUR = 27;
    const CSV_ELEVEUR = 28;




    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('file_path', sfCommandArgument::REQUIRED, "Fichier csv pour l'import")
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'entite-from-csv';
        $this->briefDescription = "Import d'une entite";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $this->file_path = $arguments['file_path'];

        error_reporting(E_ERROR | E_PARSE);

        $this->import();

    }

    protected function import(){

      if(!$this->file_path){
        throw new  sfException("Le paramètre du fichier csv doit être renseigné");

      }
      error_reporting(E_ERROR | E_PARSE);

      foreach(file($this->file_path) as $line) {
        if(!preg_match("^EVV principal;Siret;Forme;Nom relation;Catégorie;", $line)){
            $line = str_replace("\n", "", $line);
            $this->importEntite($line);
          }
        }
    }

    protected function importEntite($line){

            $data = str_getcsv($line, ';');
            $identifiant = sprintf("%06d",intval($data[self::CSV_IDENTIFIANT_LIGNE]));

            $soc = SocieteClient::getInstance()->find($identifiant);
            if(!$soc){
                $soc = $this->importSociete($data,$identifiant);
                $etb = $this->importEtablissement($soc,$data,$identifiant);
                $compte = $this->importContactAssocie($soc,$data,$identifiant);
                echo "\n";
            }else{
               $etb = $soc->getEtablissementPrincipal();
               echo "La société : ".$identifiant." est déjà dans la base\n";
            }
      }

    protected function importSociete($data,$identifiant){

            $societe = new societe();
            $societe->identifiant = $identifiant;
            $cvi = $data[self::CSV_EVV];
            $societe->type_societe = SocieteClient::TYPE_OPERATEUR ;
            $societe->constructId();

            $societe->raison_sociale = $this->buildRaisonSociete($data);

            $societe->add('date_creation', date("Y-m-d"));
            if($date_creation = trim($data["CSV_DATE_CREATION"]) != "00/00/00"){
              $societe->add('date_creation', date("Y-m-d",DateTime::createFromFormat("d/m/y",$date_creation)));
            }

            $societe->code_comptable_client = null; // pas de code comptable?

            $siege = $societe->getOrAdd('siege');
            $societe->siret = ($data[self::CSV_SIRET])? $data[self::CSV_SIRET] : null;

            $societe->siege->adresse = $data[self::CSV_ADRESSE_1];
            $societe->siege->adresse_complementaire = $data[self::CSV_ADRESSE_2];

            $societe->siege->code_postal = $data[self::CSV_CP];
            $societe->siege->commune = $data[self::CSV_VILLE];

            $societe->siege->pays = "France";

            $telephone1 = $this->formatTel($data[self::CSV_TELEPHONE_1]);
            $telephone2 = $this->formatTel($data[self::CSV_TELEPHONE_2]);

            if($telephone1 && preg_match("/^(06|07)/",$telephone1)){
              $societe->telephone_mobile = $telephone1;
            }
            if($telephone1 && preg_match("/^(01|02|03|04|05|09)/",$telephone1)){
              $societe->telephone_bureau = $telephone1;
            }

            if($telephone2 && preg_match("/^(06|07)/",$telephone2)){
              if(!$societe->telephone_mobile){
                $societe->telephone_mobile = $telephone2;
              }else{
                $societe->fax = $telephone2;
              }
            }

            if($telephone2 && preg_match("/^(01|02|03|04|05|09)/",$telephone2)){
              if(!$societe->telephone_bureau){
                $societe->telephone_bureau = $telephone2;
              }else{
                $societe->fax = $telephone2;
              }
            }

            $societe->email = str_replace("'","",trim($data[self::CSV_EMAIL]));

            $societe->save();
            $societe = SocieteClient::getInstance()->find($societe->_id);
            return $societe;
          }

    protected function importEtablissement($societe,$data,$identifiant){

          $type_etablissement = EtablissementFamilles::FAMILLE_PRODUCTEUR;
          // Type d'établissement????

          $cvi = $data[self::CSV_EVV];
          $etablissement = $societe->createEtablissement($type_etablissement);
          $etablissement->constructId();
          $etablissement->cvi = $cvi;
          $etablissement->nom = $this->buildRaisonSociete($data);

          $etablissement->region = "PDL"; //Comment on determine la région?

          $etablissement->save();

          echo "L'entité $identifiant CVI (".$cvi.")  etablissement =>  $etablissement->_id  \n";
          if(trim($data[self::CSV_OBSERVATION])){
              $etablissement->setCommentaire($data[self::CSV_OBSERVATION]);
          }
          $etablissement->save();

          return $etablissement;
        }

    protected function importContactAssocie($societe,$data){

      $contact = CompteClient::getInstance()->createCompteInterlocuteurFromSociete($societe);
      $contact->nom = trim($data[self::CSV_CONTACT_NOM]);
      $contact->prenom = trim($data[self::CSV_CONTACT_PRENOM]);
      $contact->fonction = trim($data[self::CSV_CONTACT_FONCTION]);
      $contact->telephone = $data[self::CSV_CONTACT_TELEPHONE];
      $contact->save();

    }

    protected function buildRaisonSociete($data){
      if($data[self::CSV_FORME_SOCIETE]){
        return trim($data[self::CSV_FORME_SOCIETE]).' '.trim($data[self::CSV_RAISONSOCIALE]);
      }
      return trim($data[self::CSV_RAISONSOCIALE]);
    }

}

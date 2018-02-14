<?php

class importEntitesFromCSVTask extends sfBaseTask
{

    protected $file_path = null;
    protected $chaisAttributsInImport = array();

    const CSV_OLDID = 0;
    const CSV_TITRE = 1;
    const CSV_NOM = 2;
    const CSV_ADRESSE_1 = 3;
    const CSV_ADRESSE_2 = 4;
    const CSV_ADRESSE_3 = 5;
    const CSV_CP = 6;
    const CSV_VILLE = 7;


    const CSV_EVV = 8;
    const CSV_SIRET = 9;

    const CSV_TELEPHONE = 10;
    const CSV_PORTABLE = 11;
    const CSV_FAX = 12;
    const CSV_EMAIL = 13;

    const CSV_ACTIVITES = 14;
    const CSV_ETAT = 15;
    const CSV_TYPE = 16;
    const CSV_CHAIS_ACTIVITES = 17;


    const CSV_CHAIS_ADRESSE_1 = 18;
    const CSV_CHAIS_ADRESSE_2 = 19;
    const CSV_CHAIS_ADRESSE_3 = 20;
    const CSV_CHAIS_CP = 21;
    const CSV_CHAIS_VILLE = 22;


    const CSV_CAVE_APPORTEURID = 23;
    const CSV_CAVE_COOP = 24;



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
      $this->chaisAttributsInImport = EtablissementClient::$chaisAttributsInImport;

      foreach(file($this->file_path) as $line) {
          $line = str_replace("\n", "", $line);
          if(preg_match("/^\"tbl_CDPOps.IdOP/", $line)) {
              continue;
          }
          $this->importEntite($line);
        }
    }


    protected function importEntite($line){
            $data = str_getcsv($line, ';');
            $oldId = $data[self::CSV_OLDID];
            $identifiant = sprintf("%06d",intval(preg_replace("/CDP/","",$oldId)));

            $soc = SocieteClient::getInstance()->find($identifiant);
            if(!$soc){
                $soc = $this->importSociete($data,$identifiant);
                $etb = $this->importEtablissement($soc,$data,$identifiant);
                $this->addChaiForEtablissement($etb,$data);
            }else{
              $etb = $soc->getEtablissementPrincipal();
              echo "La société : ".$identifiant." est déjà dans la base => on va alimenter les chais\n";
              $this->addChaiForEtablissement($etb,$data);
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

            $societe->code_comptable_client = $societe->identifiant;
            $siege = $societe->getOrAdd('siege');

            $societe->siret = ($data[self::CSV_SIRET])? $data[self::CSV_SIRET] : null;

            $societe->siege->adresse = $data[self::CSV_ADRESSE_1];
            $societe->siege->adresse_complementaire = $data[self::CSV_ADRESSE_2];

            if($data[self::CSV_ADRESSE_3]){
              $societe->siege->adresse_complementaire .= " − ".$data[self::CSV_ADRESSE_3];
            }
            $societe->siege->code_postal = $data[self::CSV_CP];
            $societe->siege->commune = $data[self::CSV_VILLE];
            if($data[self::CSV_CP]){
              $societe->siege->pays = "France";
            }else{
              $societe->siege->pays = ($data[self::CSV_ADRESSE_3])? $data[self::CSV_ADRESSE_3] : 'Autre Pays';
            }

            $societe->telephone = $data[self::CSV_TELEPHONE];
            $societe->fax = $data[self::CSV_FAX];
            $societe->email = $data[self::CSV_EMAIL];

            if($data[self::CSV_ETAT] == "Archivé"){
              $societe->setStatut(SocieteClient::STATUT_SUSPENDU);
            }else{
              $societe->setStatut(SocieteClient::STATUT_ACTIF);
            }

            $societe->save();
            $societe = SocieteClient::getInstance()->find($societe->_id);
            return $societe;
          }

    protected function importEtablissement($societe,$data,$identifiant){
          $type_etablissement = EtablissementFamilles::FAMILLE_PRODUCTEUR;

          $cvi = $data[self::CSV_EVV];

          $etablissement = $societe->createEtablissement($type_etablissement);
          $etablissement->constructId();
          $etablissement->cvi = $cvi;
          $etablissement->nom = $this->buildRaisonSociete($data);
          $etablissement->save();

          $compte = $societe->getMasterCompte();
          $compte->nom = $this->buildRaisonSociete($data);
          $compte->updateNomAAfficher();
          $compte->email = $societeCommunication[self::CSV_EMAIL];
          $compte->telephone = $societeCommunication[self::CSV_TELEPHONE];
          $compte->telephone_mobile = $societeCommunication[self::CSV_PORTABLE];

          $compte->fonction = "";
          $compte->num_interne = $identifiant;
          $compte->save();

          echo "L'entité $identifiant CVI (".$cvi.")  Compte =>  $compte->_id \n";
          return $etablissement;

        }

        protected function addChaiForEtablissement($etb,$data){
          $newChai = $etb->getOrAdd('chais')->add();
          $newChai->nom = $data[self::CSV_CHAIS_VILLE];
          $newChai->adresse = $data[self::CSV_CHAIS_ADRESSE_1];
          if($data[self::CSV_CHAIS_ADRESSE_2]) $newChai->adresse .=' - '.$data[self::CSV_CHAIS_ADRESSE_2];
          if($data[self::CSV_CHAIS_ADRESSE_3]) $newChai->adresse .=' - '.$data[self::CSV_CHAIS_ADRESSE_3];
          $newChai->commune = $data[self::CSV_CHAIS_VILLE];
          $newChai->code_postal = $data[self::CSV_CHAIS_CP];
          $activites = explode(';',$data[self::CSV_ACTIVITES]);
          foreach ($activites as $activite) {
            if(!array_key_exists(trim($activite),$this->chaisAttributsInImport)){
              var_dump($activite); exit;
            }
            $activiteKey = $this->chaisAttributsInImport[trim($activite)];
            $newChai->getOrAdd('attributs')->add($activiteKey,EtablissementClient::$chaisAttributsLibelles[$activiteKey]);
          }
          $etb->save();
          return $etb;
        }

    protected function buildRaisonSociete($data){
      $civilites = array("MR","MME", "MM", "M");
      if(in_array($data[self::CSV_TITRE],$civilites)){
        return $data[self::CSV_NOM].' ('.$data[self::CSV_TITRE].')';
      }
      return $data[self::CSV_TITRE].' '.$data[self::CSV_NOM];
    }

}

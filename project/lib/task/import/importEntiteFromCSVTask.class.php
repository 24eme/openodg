<?php

class importEntitesFromCSVTask extends sfBaseTask
{

    protected $file_path = null;
    protected $chaisAttributsInImport = array();
    protected $isSuspendu = false;

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
    const CSV_ORDRE = 16;
    const CSV_ZONE = 17;
    const CSV_ID_TIERS = 18;
    const CSV_TYPE = 19;
    const CSV_CHAIS_ACTIVITES = 20;


    const CSV_CHAIS_ADRESSE_1 = 21;
    const CSV_CHAIS_ADRESSE_2 = 22;
    const CSV_CHAIS_ADRESSE_3 = 23;
    const CSV_CHAIS_CP = 24;
    const CSV_CHAIS_VILLE = 25;


    const CSV_CAVE_APPORTEURID = 26;
    const CSV_CAVE_COOP = 27;
    const CSV_SOCIETE_TYPE = 28;



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
        echo "\n ** AJOUT DES LIAISONS CAVECOOP ET NEGOCE  **\n";
        foreach(file($this->file_path) as $line) {
            $line = str_replace("\n", "", $line);
            if(preg_match("/^\"tbl_CDPOps.IdOP/", $line)) {
                continue;
            }
            $this->importLiaisons($line);
          }
    }


    protected function importEntite($line){
            $data = str_getcsv($line, ';');
            if(!preg_match('/^'.SocieteClient::getInstance()->getSocieteFormatIdentifiantRegexp().'$/', $data[self::CSV_OLDID])) {
                throw new Exception("Mauvais identifiant ". $data[self::CSV_OLDID]);
            }
            $identifiant = $data[self::CSV_OLDID];

            $this->isSuspendu = ($data[self::CSV_ETAT] == "Archivé");

            $soc = SocieteClient::getInstance()->find($identifiant);
            if(!$soc){
                $soc = $this->importSociete($data,$identifiant);
                $etb = $this->importEtablissement($soc,$data,$identifiant);
                $etb = EtablissementClient::getInstance()->find($etb->_id);
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

            $societe->telephone_bureau = $data[self::CSV_TELEPHONE];
            $societe->fax = $data[self::CSV_FAX];
            $emails = (explode(";",$data[self::CSV_EMAIL]));

            $societe->email = trim($emails[0]);

            if($this->isSuspendu){
              $societe->setStatut(SocieteClient::STATUT_SUSPENDU);
            }else{
              $societe->setStatut(SocieteClient::STATUT_ACTIF);
            }
            $societe->save();
            $societe = SocieteClient::getInstance()->find($societe->_id);
            if(count($emails) > 1){
                foreach ($emails as $key => $email) {
                    if(!$key){
                        continue;
                    }
                    $compte = CompteClient::getInstance()->createCompteInterlocuteurFromSociete($societe);
                    $compte->nom = "Autre Contact";
                    $compte->email = trim($email);
                    echo "L'entité $societe->_id a un interlocuteur $compte->_id ".$compte->nom." (".$compte->email.")\n";
                    $compte->save();
                    $societe = SocieteClient::getInstance()->find($societe->_id);
                }
            }
            return $societe;
          }

    protected function importEtablissement($societe,$data,$identifiant){
          $type_etablissement = EtablissementFamilles::FAMILLE_PRODUCTEUR;

          $cvi = $data[self::CSV_EVV];
          if($data[self::CSV_SOCIETE_TYPE]){
              $type_etablissement = $data[self::CSV_SOCIETE_TYPE];
          }
          $etablissement = $societe->createEtablissement($type_etablissement);
          $etablissement->constructId();
          $etablissement->cvi = $cvi;
          $etablissement->nom = $this->buildRaisonSociete($data);
          $etablissement->save();
          if($this->isSuspendu){
            $etablissement->setStatut(SocieteClient::STATUT_SUSPENDU);
          }else{
            $etablissement->setStatut(SocieteClient::STATUT_ACTIF);
          }


          echo "L'entité $identifiant CVI (".$cvi.")  etablissement =>  $etablissement->_id";
          echo ($this->isSuspendu)? " SUSPENDU \n" : " ACTIF \n";
          $etablissement->save();

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

    protected function importLiaisons($line){
        $data = str_getcsv($line, ';');
        if($data[self::CSV_CAVE_APPORTEURID]){
            $viti = EtablissementClient::getInstance()->findByIdentifiant($data[self::CSV_OLDID]."01");
            $coopOrNego = EtablissementClient::getInstance()->findByIdentifiant($data[self::CSV_CAVE_APPORTEURID]."01");
            if(!$viti){
                echo "/!\ viti non trouvé : ".$data[self::CSV_OLDID]."\n";
                return false;
            }
            if(!$coopOrNego){
                echo "/!\ cave coop ou négo non trouvé : ".$data[self::CSV_CAVE_APPORTEURID]."\n";
                return false;
            }
            if($coopOrNego->_id == $viti->_id){
                echo "/!\ Liaison sur lui même trouvée : ".$data[self::CSV_CAVE_APPORTEURID]."\n";
                return false;
            }
            if($coopOrNego->isNegociant()){
                $viti->addLiaison(EtablissementClient::TYPE_LIAISON_NEGOCIANT,$coopOrNego,true);
            }
            if($coopOrNego->isCooperative()){
                $viti->addLiaison(EtablissementClient::TYPE_LIAISON_COOPERATIVE,$coopOrNego,true);
            }
            $viti->save();
            echo $viti->_id." ".$coopOrNego->_id." isNEgo : ".$coopOrNego->isNegociant()."\n";

        }
    }

}

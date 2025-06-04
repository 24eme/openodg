<?php

class importEntitesFromCSVTask extends sfBaseTask
{

    protected $file_path = null;
    protected $negoce_file_path = null;
    protected $file_archives = null;

    protected $chaisAttributsInImport = array();
    protected $negoces = array();
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


    const CSV_ARCHIVE_CVI = 0;
    const CSV_ARCHIVE_NOM = 1;

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('file_path', sfCommandArgument::REQUIRED, "Fichier csv pour l'import"),
            new sfCommandArgument('negoce_file_path', sfCommandArgument::REQUIRED, "Fichier csv des negociant pour l'import"),
            new sfCommandArgument('file_archives', sfCommandArgument::OPTIONAL, "Fichier csv des operateurs archivés")
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
        $this->negoce_file_path = $arguments['negoce_file_path'];
        $this->file_archives = $arguments['file_archives'];
        error_reporting(E_ERROR | E_PARSE);

        $this->import();

    }

    protected function import(){

      if(!$this->file_path || !$this->negoce_file_path){
        throw new  sfException("Le paramètre du fichier csv doit être renseigné");

      }
      error_reporting(E_ERROR | E_PARSE);

      $this->negoces["cvi"] = array();
      $this->negoces["siret"] = array();
      foreach(file($this->negoce_file_path) as $line) {
        $data = str_getcsv($line, ';');
        if($cvi = trim($data[2])){
          $this->negoces["cvi"][] = $cvi;
        }
        if($siret = trim($data[3])){
          $this->negoces["siret"][] = $siret;
        }
      }

      foreach(file($this->file_path) as $line) {
        if(!preg_match("/^Identifiant ligne/", $line)){
            $line = str_replace("\n", "", $line);
            $this->importEntite($line);
          }
        }

        foreach (file($this->file_archives) as $line) {
          if(!preg_match("/^EVV principal;Nom relation/", $line)){
              $line = str_replace("\n", "", $line);
              $this->importArchives($line);
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
            if(trim($data["CSV_DATE_CREATION"]) && trim($data["CSV_DATE_CREATION"]) != "00/00/00"){
              $societe->add('date_creation', date("Y-m-d",DateTime::createFromFormat("d/m/y",trim($data["CSV_DATE_CREATION"]))));
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
          if(in_array($data[self::CSV_EVV],$this->negoces["cvi"]) || in_array($data[self::CSV_SIRET],$this->negoces["siret"])){
            $type_etablissement = EtablissementFamilles::FAMILLE_NEGOCIANT;
          }

          $cvi = $data[self::CSV_EVV];
          $etablissement = $societe->createEtablissement($type_etablissement);
          $etablissement->constructId();
          $etablissement->cvi = $cvi;
          $etablissement->nom = $this->buildRaisonSociete($data);

          $ppm = str_replace("ppm_","",$data[self::CSV_PPM]);
          if($ppm){
            $etablissement->ppm = $ppm;
          }

          if(count(EtablissementClient::getSecteurs()) == 1){
            $regions = array_keys(EtablissementClient::getSecteurs());
            $etablissement->region = $regions[0];
          }

          $etablissement->save();
          if($cvi){
            echo "L'entité $identifiant CVI (".$etablissement->cvi.")  etablissement =>  $etablissement->_id  \n";
          }else{
            echo "L'entité $identifiant Siret (".$societe->siret.")  etablissement =>  $etablissement->_id  \n";
          }

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

    protected function importArchives($line){

            $data = str_getcsv($line, ';');

            $rs = $data[self::CSV_ARCHIVE_NOM];
            $cvi = trim($data[self::CSV_ARCHIVE_CVI]);
            $etbExist = EtablissementClient::getInstance()->findByCvi($cvi,true);
            if($rs && $cvi && !$etbExist){
              $societe = SocieteClient::getInstance()->createSociete($rs);
              $societe->type_societe = SocieteClient::TYPE_OPERATEUR ;
              $societe->add('date_creation', date("Y-m-d"));
              $societe->statut = SocieteClient::STATUT_SUSPENDU;
              $societe->save();

              $type_etablissement = EtablissementFamilles::FAMILLE_PRODUCTEUR;
              if(in_array($cvi,$this->negoces["cvi"])){
                $type_etablissement = EtablissementFamilles::FAMILLE_NEGOCIANT;
              }

              $etablissement = $societe->createEtablissement($type_etablissement);
              $etablissement->constructId();
              $etablissement->cvi = $cvi;
              $etablissement->statut = SocieteClient::STATUT_SUSPENDU;
              $etablissement->nom = $rs;

              if(count(EtablissementClient::getSecteurs()) == 1){
                $regions = array_keys(EtablissementClient::getSecteurs());
                $etablissement->region = $regions[0];
              }
              $etablissement->save();
              echo "L'entité $etablissement->identifiant CVI (".$etablissement->cvi.")  etablissement =>  $etablissement->_id  \n";
            }
      }

    protected function buildRaisonSociete($data){
      if($data[self::CSV_FORME_SOCIETE]){
        return trim($data[self::CSV_FORME_SOCIETE]).' '.trim($data[self::CSV_RAISONSOCIALE]);
      }
      return trim($data[self::CSV_RAISONSOCIALE]);
    }

    protected function formatTel($tel){

        if(!$tel){
            return null;
        }
        $t = str_replace(array(' ','.'),array('',''),$tel);
        if(strlen($t) > 10 && preg_match("/^\+?33/",$t)){
          $t = preg_replace("/^\+?33(.+)/","0$1", $t);
        }
        $tk = sprintf("%010d",$t);
        return substr($tk, 0,2)." ".substr($tk,2,2)." ".substr($tk,4,2)." ".substr($tk,6,2)." ".substr($tk,8,2);
    }

////////////////////////////////////// ci dessous probablement inutil


    protected function addChaiForEtablissement($etb,$data){

          $newChai = $etb->getOrAdd('chais')->add();
          $newChai->nom = $data[self::CSV_CHAIS_VILLE];
          $newChai->adresse = $this->getChaiAdresseConcat($data);

          $newChai->commune = $data[self::CSV_CHAIS_VILLE];
          $newChai->code_postal = $data[self::CSV_CHAIS_CP];
          $newChai->archive = $data[self::CSV_CHAI_ARCHIVE];
          $activites = explode(';',$data[self::CSV_CHAIS_ACTIVITES]);
          foreach ($activites as $activite) {
            if(!array_key_exists(trim($activite),$this->chaisAttributsInImport)){
              var_dump($activite); exit;
            }
            $activiteKey = $this->chaisAttributsInImport[trim($activite)];
            $newChai->getOrAdd('attributs')->add($activiteKey,EtablissementClient::$chaisAttributsLibelles[$activiteKey]);
          }
          echo " LE CHAI ".$newChai->nom." ".$newChai->adresse."...  a été crée   ";
          $etb->save();
          return $etb;

        }


    protected function importLiaisons($viti,$line){
        $data = str_getcsv($line, ';');
        if($data[self::CSV_CAVE_APPORTEURID]){
            $coopOrNego = EtablissementClient::getInstance()->findByIdentifiant($data[self::CSV_CAVE_APPORTEURID]."01");
            if(!$viti){
                echo "\n/!\ viti non trouvé : ".$data[self::CSV_OLDID]."\n";
                return false;
            }
            if(!$coopOrNego){
                echo "\n/!\ cave coop ou négo non trouvé : ".$data[self::CSV_CAVE_APPORTEURID]."\n";
                return false;
            }
            if($coopOrNego->_id == $viti->_id){
                echo "\n/!\ Liaison sur lui même trouvée : ".$data[self::CSV_CAVE_APPORTEURID]."\n";
                return false;
            }

            if($data[self::CSV_CHAIS_TYPE] == "Apporteur"){
                $attributs_chai = array(EtablissementClient::CHAI_ATTRIBUT_APPORT);
                if($coopOrNego->isCooperative()){
                    $chaiAssocie = $this->getChaiAssocie($data,$coopOrNego);
                    $viti->addLiaison(EtablissementClient::TYPE_LIAISON_COOPERATIVE,$coopOrNego,true,$chaiAssocie,$attributs_chai);
                }elseif($coopOrNego->isNegociant()) {
                    $chaiAssocie = $this->getChaiAssocie($data,$coopOrNego);
                    $viti->addLiaison(EtablissementClient::TYPE_LIAISON_NEGOCIANT,$coopOrNego,true,$chaiAssocie,$attributs_chai);
                }elseif($coopOrNego->isNegociantVinificateur()) {
                    $chaiAssocie = $this->getChaiAssocie($data,$coopOrNego);
                    $viti->addLiaison(EtablissementClient::TYPE_LIAISON_NEGOCIANT_VINIFICATEUR,$coopOrNego,true,$chaiAssocie,$attributs_chai);
                }
            }else{
                $chaiAssocie = $this->getChaiAssocie($data,$coopOrNego);
                $attributs_chai = $this->convertAttributsChais(explode(';',$data[self::CSV_CHAIS_ACTIVITES]));
                $viti->addLiaison(EtablissementClient::TYPE_LIAISON_HEBERGE_TIERS,$coopOrNego,true,$chaiAssocie,$attributs_chai);
            }

            $viti->save();
            $type = ($coopOrNego->isNegociant())? ' (négociant) ' : ' (coopérative)';
            echo " LA LIAISON ".$viti->_id." ".$coopOrNego->_id." ".$type.' a été créée   ';

        }
    }

    protected function getChaiAssocie($data,$coopOrNego){
        if(!$coopOrNego->exist('chais') || !$coopOrNego->chais){
            return null;
        }
        $chais = $coopOrNego->getChais();
        if(count($chais) == 1){
            foreach ($chais as $chai) {
                return $chai;
            }
        }
        foreach ($coopOrNego->getChais() as $key => $chai) {
            $adresse = str_replace('BOULEVARD','BD',$data[self::CSV_CHAIS_ADRESSE_2]);
            if($data[self::CSV_CHAIS_ADRESSE_3]){
                $adresse .= " - ".str_replace('BOULEVARD','BD',$data[self::CSV_CHAIS_ADRESSE_3]);
            }
            if($adresse == str_replace('BOULEVARD','BD',$chai->adresse)
                && ($data[self::CSV_CHAIS_VILLE] == $chai->commune)
                && ($data[self::CSV_CHAIS_CP] == $chai->code_postal)){
                    return $chai;
            }
        }
        echo "\n/!\ ".$data[self::CSV_OLDID]." : on ne trouve pas le chai ".$data[self::CSV_CHAIS_ADRESSE_1] ." ".$data[self::CSV_CHAIS_ADRESSE_2]." ".$data[self::CSV_CHAIS_ADRESSE_3]." ".$data[self::CSV_CHAIS_VILLE]." ".$data[self::CSV_CHAIS_CP]. " dans ".$coopOrNego->_id."\n";

        return null;
    }

    protected function convertAttributsChais($chaisAttributsCsv){
        $attributsConverted = array();
        foreach ($chaisAttributsCsv as $chaiCsv) {
            $attributsConverted[] = $this->convert_attributs[trim($chaiCsv)];
        }
        return $attributsConverted;
    }


    protected function getChaiAdresseConcat($data){
        $adresse = $data[self::CSV_CHAIS_ADRESSE_1];
        if($data[self::CSV_CHAIS_ADRESSE_2]) $adresse .=' - '.$data[self::CSV_CHAIS_ADRESSE_2];
        if($data[self::CSV_CHAIS_ADRESSE_3]) $adresse .=' - '.$data[self::CSV_CHAIS_ADRESSE_3];
        return $adresse;
    }


    protected function addResponsableDeChai($societe,$data){
        if(!$data[self::CSV_CHAI_RESPONSABLE_NOM] && !$data[self::CSV_CHAI_RESPONSABLE_TELEPHONE]){
            return;
        }
        $nom = trim($data[self::CSV_CHAI_RESPONSABLE_NOM]);
        $telephone = trim($this->formatTel($data[self::CSV_CHAI_RESPONSABLE_TELEPHONE]));

        if(!$nom){
            $nom = "AUTRE CONTACT";
        }

        $contact = CompteClient::getInstance()->createCompteInterlocuteurFromSociete($societe);
        $contact->nom = $nom;
        $contact->fonction = "Responsable de chais";
        $contact->adresse = $data[self::CSV_CHAIS_ADRESSE_1];

        $adresse_complementaire = "";
        if($data[self::CSV_CHAIS_ADRESSE_2]) $adresse_complementaire .= $data[self::CSV_CHAIS_ADRESSE_2];
        if($data[self::CSV_CHAIS_ADRESSE_3]) $adresse_complementaire .=' - '.$data[self::CSV_CHAIS_ADRESSE_3];

        $contact->adresse_complementaire = $adresse_complementaire;
        $contact->code_postal = $data[self::CSV_CHAIS_CP];
        $contact->commune = $data[self::CSV_CHAIS_VILLE];

        if(preg_match('/^(06|07)/',$telephone)){
            $contact->telephone_mobile = $telephone;
        }else{
            $contact->telephone_bureau = $telephone;
        }

        echo " (+INTERLOCUTEUR $contact->_id ".$contact->nom." (".$contact->telephone_bureau."/".$contact->telephone_mobile.") ";


        $contact->save();
    }

}

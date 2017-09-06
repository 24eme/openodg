<?php

class importEntiteFromXmlTask extends sfBaseTask
{

    protected $observationsCodifieesArr = array();

    protected $identifiant = null;
    protected $cvi = null;

    protected $nom = null;
    protected $prenom = null;
    protected $civilite = null;

    protected $adresse1 = null;
    protected $adresse2 = null;
    protected $adresse3 = null;
    protected $adresseEtrangère = null;
    protected $cleCoordonnee = null;
    protected $canton = null;
    protected $codePostal = null;
    protected $commune = null;
    protected $communeLibelle = null;
    protected $libelleACheminement = null;
    protected $pays = null;

    protected $email = null;
    protected $fax = null;
    protected $numeroCommunication = null;
    protected $portable = null;
    protected $site_web = null;
    protected $telephone = null;
    protected $type_contact = null;

    protected $siret = null;
    protected $type_etablissement = null;
    protected $date_archivage = null;
    protected $date_modification = null;
    protected $date_creation = null;
    protected $groupe = null;
    protected $entite_juridique = null;



    protected $observationsCodifiees = array();

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('file_path', sfCommandArgument::REQUIRED, "Fichier xml pour l'import")
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'entite-from-xml';
        $this->briefDescription = "Import d'une entite";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $file_path = $arguments['file_path'];

        error_reporting(E_ERROR | E_PARSE);

        $xml_content_str = file_get_contents($file_path);

        $xmlEntite = new SimpleXMLElement($xml_content_str);

        $path_obs = dirname(__FILE__)."/../../../data/configuration/rhone/observationsCodifiees.csv";
        $observationsCodifieesCsv = new CsvFile($path_obs);

        foreach ($observationsCodifieesCsv->getCsv() as $row) {
          $this->observationsCodifieesArr[$row[0]] = $row[1];
        }

        foreach ($xmlEntite as $nameField => $field) {

            $this->searchIdentifiant($nameField,$field);
            $this->searchType($nameField,$field);
            $this->searchDates($nameField,$field);
            $this->searchCvi($nameField,$field);
            $this->searchNomPrenom($nameField,$field);
            $this->searchCoordonnees($nameField,$field);
            $this->searchCommunications($nameField,$field);
            $this->searchObservationsCodifiees($nameField,$field);
            $this->searchSiret($nameField,$field);

        }

        $this->importEntite();


    }

    public function searchIdentifiant($nameField, $field){
      if($nameField == "b:CleIdentite"){
        if(count($field)){
          if(count($field) > 1){ var_dump($nameField,$field); continue; }
        }
        $this->identifiant = (string) $field;
      }
    }

    public function searchType($nameField, $field){
      $this->type_etablissement = "PRODUCTEUR";

      if($nameField == "b:Type"){
        if(count($field)){
          if(count($field) > 1){ var_dump($nameField,$field); continue; }
        }
        switch ((string) $field) {
          case 'P':
          $this->entite_juridique = "Physique";
          break;

          case 'M':
          $this->entite_juridique = "Morale";
          break;
        }
      }
    }


    public function searchDates($nameField, $field){
       if($nameField == "b:DateArchivage"){
          if(count($field)){
            if(count($field) > 1){ var_dump($nameField,$field); continue; }
          }
          $date = (string) $field;
          if($date != "0001-01-01T00:00:00"){
            $this->date_archivage = (new DateTime($date))->format("Y-m-d");
          }
        }
        if($nameField == "b:DateModification"){
           if(count($field)){
             if(count($field) > 1){ var_dump($nameField,$field); continue; }
           }
           $date = (string) $field;
           if($date != "0001-01-01T00:00:00"){
             $this->date_modification = (new DateTime($date))->format("Y-m-d");
           }
         }
         if($nameField == "b:DateCreation"){
            if(count($field)){
              if(count($field) > 1){ var_dump($nameField,$field); continue; }
            }
            $date = (string) $field;
            if($date != "0001-01-01T00:00:00"){
              $this->date_creation = (new DateTime($date))->format("Y-m-d");
            }
          }
    }

    public function searchSiret($nameField, $field){
      if($nameField == "b:Siret"){
        if(count($field)){
          if(count($field) > 1){ var_dump($nameField,$field); continue; }
        }
        $this->siret = (string) $field;
      }
    }

  public function searchCvi($nameField, $field){
            if($nameField == "b:Evv" && boolval((string) $field)){
              $evvStr = (string) $field;
                $evvArray = explode(',',$evvStr);
                if(count($evvArray)){
                  $cviPrec = "";
                  $cviReal = "";
                  foreach ($evvArray as $cvi_c) {
                    if($cvi_c){
                      $cviReal = $cvi_c;
                      if($cviPrec && $cviPrec != $cvi_c){
                        echo "l'identité  ".  $this->identifiant." a des cvis différents : ".$evvStr."\n";
                      }
                      $cviPrec = $cvi_c;
                    }
                  }
                }
                $this->cvi = $cviReal;
            }
    }

    protected function searchNomPrenom($nameField, $field){
          if($nameField == "b:Prenom"){
              $this->prenom = (string) $field;
          }
          if($nameField == "b:RaisonSociale"){
              $this->nom = (string) $field;
          }
          if($nameField == "b:Titre"){
              if((string) $field){
                $this->civilite = (string) $field.".";
              }
          }
    }

    protected function buildRaisonSociete(){
      $raison_sociale = ($this->civilite)? $this->civilite." " : "";
      $raison_sociale .= ($this->prenom)? $this->prenom." " : "";;
      $raison_sociale .= $this->nom;
      return $raison_sociale;
    }

    protected function searchCoordonnees($nameField, $field){
            if($nameField == "b:Coordonnees"){
              $coordonneesArray = ((array) $field);
              if(array_key_exists("b:Identite_Coordonnee",$coordonneesArray)){
                $coords = (array) $coordonneesArray["b:Identite_Coordonnee"];
                if(array_key_exists("b:Adresse1",$coords)){
                  $this->adresse1 = (string) $coords["b:Adresse1"];
                }
                if(array_key_exists("b:Adresse2",$coords)){
                  $this->adresse2 = (string) $coords["b:Adresse2"];
                }
                  if(array_key_exists("b:Adresse3",$coords)){
                  $this->adresse2 = (string) $coords["b:Adresse3"];
                }
                  if(array_key_exists("b:AdresseEtrangere",$coords)){
                  $this->adresseEtrangere = filter_var(((string) $coords["b:AdresseEtrangere"]), FILTER_VALIDATE_BOOLEAN);
                  $this->canton = (string) $coords["b:Canton"];
                }
                  if(array_key_exists("b:CleCoordonnee",$coords)){
                  $this->cleCoordonnee = (string) $coords["b:CleCoordonnee"];
                }
                  if(array_key_exists("b:Canton",$coords)){
                  $this->canton = (string) $coords["b:Canton"];
                }
                  if(array_key_exists("b:CodePostal",$coords)){
                  $this->codePostal = (string) $coords["b:CodePostal"];
                }
                  if(array_key_exists("b:Commune",$coords)){
                  $this->commune = (string) $coords["b:Commune"];
                }
                  if(array_key_exists("b:CommuneLibelle",$coords)){
                  $this->communeLibelle = (string) $coords["b:CommuneLibelle"];
                }
                  if(array_key_exists("b:LibelleACheminement",$coords)){
                  $this->libelleACheminement = (string) $coords["b:LibelleACheminement"];
                }
                if(array_key_exists("b:Pays",$coords)){
                  $this->pays = (string) $coords["b:Pays"];
                }
              }
            }
    }

    protected function searchCommunications($nameField, $field){
            if($nameField == "b:Communications"){
              $communicationsArray = ((array) $field);
              if(array_key_exists("b:Identite_Communication",$communicationsArray)){
                $comms = (array) $communicationsArray["b:Identite_Communication"];
                if(array_key_exists("b:Email",$comms)){
                  $this->email = (string) $comms["b:Email"];
                }
                if(array_key_exists("b:Fax",$comms)){
                  $this->fax = (string) $comms["b:Fax"];
                  if(!$this->fax || ($this->fax == "__.__.__.__.__")){ $this->fax = null; }
                }
                if(array_key_exists("b:NumeroCommunication",$comms)){
                  $this->numeroCommunication = (string) $comms["b:NumeroCommunication"];
                }
                if(array_key_exists("b:Portable",$comms)){
                  $this->portable = (string) $comms["b:Portable"];
                  if(!$this->portable || ($this->portable == "__.__.__.__.__")){ $this->portable = null; }
                }
                if(array_key_exists("b:SiteWeb",$comms)){
                  $this->site_web = (string) $comms["b:SiteWeb"];
                }
                if(array_key_exists("b:Telephone",$comms)){
                  $this->telephone = (string) $comms["b:Telephone"];
                  if(!$this->telephone || ($this->telephone == "__.__.__.__.__")){ $this->telephone = null; }
                }
                if(array_key_exists("b:TypeContact",$comms)){
                  $this->type_contact = (string) $comms["b:TypeContact"];
                }

              }
            }
    }



    protected function searchObservationsCodifiees($nameField, $field){
      if($nameField == "b:ObservationCodifiee"){
        $observationsCodifieesArray = ((array) $field);
        if(array_key_exists("b:Identite_ObservationCodifiee",$observationsCodifieesArray)){
          foreach ($observationsCodifieesArray["b:Identite_ObservationCodifiee"] as $obsCodifie) {
            if(boolval((string) $obsCodifie)){
              $code = (string) $obsCodifie;
              if(!array_key_exists($code,$this->observationsCodifieesArr)){
                echo "L'identité  ".  $this->identifiant." possède une observation codifié de code ".$code." non trouvé dans les observations codifiées \n";
                continue;
              }
              $this->observationsCodifiees[$code] = $this->observationsCodifieesArr[$code];
            }
          }
        }
      }
      if($nameField == "b:LibelleGroupe"){
          $this->groupe = (string) $field;
      }
    }

    protected function importEntite(){
      $societe = new societe();
      if(!$this->identifiant){
        echo "Le fichier xml $file_path n'a pas d'identifiant!\n"; exit;
      }
        $societe->identifiant = sprintf("%06d",$this->identifiant);
        if($this->cvi){
          $societe->type_societe = "RESSORTISSANT" ;
        }else{
          $societe->type_societe =  "AUTRE" ;
        }

        $societe->constructId();
        $societe->raison_sociale = $this->buildRaisonSociete();
        $societe->add('date_modification', $this->date_modification);
        $societe->add('date_creation', $this->date_creation);
        $societe->code_comptable_client = $societe->identifiant;
        $siege = $societe->getOrAdd('siege');
        $siege->adresse = $this->adresse1;
        if($this->addresse2 || $this->adresse3){
          $siege->adresse_complementaire = $this->adresse2.($this->adresse3)? " ".$this->addresse3 : '';
        }
        $siege->code_postal = $this->codePostal;
        $siege->commune = $this->communeLibelle;
        if($this->adresseEtrangere){
          $siege->pays = $this->pays;
        }else{
          $siege->pays = "France";
        }
        $societe->telephone = $this->telephone;
        $societe->email = $this->email;
        $societe->fax = $this->fax;

        $societe->siret = $this->siret;
        if($this->date_archivage){
          $societe->setStatut(SocieteClient::STATUT_SUSPENDU);
        }
        $societe->save();

        $societe = SocieteClient::getInstance()->find($societe->_id);

        if($this->cvi){

        $etablissement = $societe->createEtablissement($this->type_etablissement);
        // $etablissement->setCompte($societe->getMasterCompte()->_id);
        $etablissement->constructId();
        $etablissement->cvi = $this->cvi;
        $etablissement->nom = $this->buildRaisonSociete();

        $this->setTags($etablissement->getMasterCompte());

        $etablissement->save();
      }

      $compte = $societe->getMasterCompte();
      $compte->nom = $this->buildRaisonSociete();
      $compte->updateNomAAfficher();
      $compte->telephone_mobile = $this->portable;
      $compte->site_internet = $this->site_web;
      $compte->fonction = $this->type_contact;
      $compte->save();

      echo "L'entité $this->identifiant CVI ($this->cvi)  C'est un compte =>  $compte->_id \n";
    }

    public function setTags($c){
      if($this->groupe){
        $c->addTag('manuel',$this->groupe);
      }
      if(count($this->observationsCodifiees)){
        echo "mis à jour des observationsCodifiees pour le compte ".  $c->_id." ";
        foreach($this->observationsCodifiees as $obsKey => $obs){
          $tag = $obs.' '.$obsKey;
          echo $tag." | ";
          $c->addTag('manuel',$tag);
        }
        echo "\n";
    }
  }

}

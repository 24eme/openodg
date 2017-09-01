<?php

class importEntiteFromXmlTask extends sfBaseTask
{

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


        foreach ($xmlEntite as $nameField => $field) {

            $this->searchIdentifiant($nameField,$field);
            $this->searchCvi($nameField,$field);
            $this->searchNomPrenom($nameField,$field);
            $this->searchCoordonnees($nameField,$field);
            $this->searchObservationsCodifiees($nameField,$field);

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

  public function searchCvi($nameField, $field){
            if($nameField == "b:Evv" && boolval((string) $field)){
              $evvStr = (string) $field;
              if(strpos($evvStr, ",")){
                $evvArray = explode(',',$evvStr);
                if(count($evvArray)){
                  $cviPrec = "";
                  $cviReal = "";
                  foreach ($evvArray as $cvi_c) {
                    if($cvi_c){
                      $cviReal = $cvi_c;
                      if($cviPrec && $cviPrec != $cvi_c){
                        echo "lidentité  ".  $this->identifiant." a des cvis différents : ".$evvStr;
                      }
                    }
                    $cviPrec = $cvi_c;
                  }
                }
                $this->cvi = $cviReal;
              }else{
                $this->cvi = (string) $evvStr;
              }
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
              $this->civilite = (string) $field;
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
                }
                if(array_key_exists("b:NumeroCommunication",$comms)){
                  $this->numeroCommunication = (string) $comms["b:NumeroCommunication"];
                }
                if(array_key_exists("b:Portable",$comms)){
                  $this->portable = (string) $comms["b:Portable"];
                }
                if(array_key_exists("b:SiteWeb",$comms)){
                  $this->site_web = (string) $comms["b:SiteWeb"];
                }
                if(array_key_exists("b:Telephone",$comms)){
                  $this->telephone = (string) $comms["b:Telephone"];
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
            if(boolval((string) $obsCodifie)){ $this->observationsCodifiees[] = (string) $obsCodifie; }
          }
        }
      }
    }

    protected function importEntite(){
      $societe = new societe();
      if(!$this->identifiant){
        echo "Le fichier xml $file_path n'a pas d'identifiant!\n"; exit;
      }
      $societe->identifiant = sprintf("%06d",$this->identifiant);

      if(!$this->cvi){
          echo "L'entité $this->identifiant n'a pas de CVI!\n"; exit;
      }
        $societe->constructId();
        $societe->raison_sociale = $this->buildRaisonSociete();

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

        $societe->save();

        $societe = SocieteClient::getInstance()->find($societe->_id);

        $etablissement = $societe->createEtablissement('PRODUCTEUR');
        // $etablissement->setCompte($societe->getMasterCompte()->_id);
        $etablissement->constructId();
        $etablissement->cvi = $this->cvi;
        if(count($this->observationsCodifiees)){
          echo "mis à jour des observationsCodifiees pour le compte ".  $etablissement->getMasterCompte()->_id." ";
          foreach($this->observationsCodifiees as $obs){
            echo $obs." | ";
            $etablissement->getMasterCompte()->addTag('observationsCodifiees',$obs);
          }
          echo "\n";
        }
        $etablissement->nom = $this->buildRaisonSociete();
        $compte = $etablissement->getMasterCompte();
        $compte->nom = $this->nom;
        $compte->prenom = $this->prenom;


        $compte->telephone_mobile = $this->portable;
        $compte->site_internet = $this->site_web ;
        $compte->fonction = $this->type_contact;

        $etablissement->save();
    }

}

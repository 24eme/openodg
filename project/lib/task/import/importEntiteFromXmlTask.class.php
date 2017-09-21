<?php

class importEntiteFromXmlTask extends sfBaseTask
{

    protected $observationsCodifieesArr = array();
    protected $groups = array();

    protected $identifiant = null;
    protected $cvi = null;

    protected $nom = null;
    protected $prenom = null;
    protected $civilite = null;

    protected $coordonnees = array();
    protected $communications = array();

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

          $this->searchSimpleStringField($nameField, $field,"b:CleIdentite","identifiant");
          $this->type_etablissement = "OPERATEUR";

          $this->searchSimpleDateField($nameField, $field, "b:DateArchivage","date_archivage");
          $this->searchSimpleDateField($nameField, $field, "b:DateModification","date_modification");
          $this->searchSimpleDateField($nameField, $field, "b:DateCreation","date_creation");

          $this->searchType($nameField,$field);
          $this->searchCvi($nameField,$field);

          $this->searchSimpleStringField($nameField, $field,"b:Prenom","prenom");
          $this->searchSimpleStringField($nameField, $field,"b:RaisonSociale","nom");
          $this->searchSimpleStringField($nameField, $field,"b:Titre","civilite");

            $this->searchCoordonnees($nameField,$field);
            $this->searchCommunications($nameField,$field);
            $this->searchObservationsCodifiees($nameField,$field);
            $this->searchSiret($nameField,$field);
            $this->searchGroups($nameField,$field);
        }

        $this->importEntite();


    }

    public function searchType($nameField, $field){
      if($nameField == "b:Type"){
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
              $pattern = array("/^,/","/,$/","/[,]{2,}/");
              $replace = array("","",",");
              $evvStrPurged = preg_replace($pattern,$replace,$evvStr);
              $evvArray = explode(',',$evvStrPurged);
              if(count($evvArray) > 2 && (count(array_unique($evvArray)) > 1)){
                echo "l'identité  ".  $this->identifiant." a des cvis différents : ".$evvStrPurged."\n";
                exit;
              }else{
                  foreach ($evvArray as $cvi_c) {
                      $this->cvi = $cvi_c;
                  }
            }
        }
   }

    protected function buildRaisonSociete(){
      $raison_sociale = ($this->civilite)? $this->civilite." " : "";
      $raison_sociale .= ($this->prenom)? $this->prenom." " : "";;
      $raison_sociale .= $this->nom;
      return $raison_sociale;
    }



    protected function searchGroups($nameField, $field){
            if($nameField == "b:Groupes"){
              $groupesArray = ((array) $field);
              foreach ($groupesArray as $key => $identiteGroup) {
                $identiteGroup = (array) $identiteGroup;
                if(count($identiteGroup) && get_class($identiteGroup[0]) == "SimpleXMLElement"){
                  foreach ($identiteGroup as $key => $identGroup) {
                    $identGroup = (array) $identGroup;
                    $this->fillGroup($identGroup);
                  }
                }else{
                    $this->fillGroup($identiteGroup);
                  }

              }
            }
    }

    protected function fillGroup($group){
      if(array_key_exists("b:CleGroupe",$group)){
      $groupeKey = (string) $group["b:CleGroupe"];
        $this->groups[$groupeKey] = array();
        $this->groups[$groupeKey]['key'] = $groupeKey;
      }else{
        echo "L'identité  ".  $this->identifiant." possède des définition de groupes mais il n'existe pas de clé de groupe :".print_r($group)." \n";
        exit;
      }
      if(array_key_exists("b:LibelleGroupe",$group)){
        $this->groups[$groupeKey]['libelle'] = (string) $group["b:LibelleGroupe"];
      }
      if(array_key_exists("b:Observations",$group)){
        $this->groups[$groupeKey]['observation'] = (string) $group["b:Observations"];
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
            }elseif((get_class($obsCodifie) == 'SimpleXMLElement') && count($obsCodifie)){
              $obsCod =(array) $obsCodifie;
              if(array_key_exists("b:ObservationCodifiee",$obsCod)){
                $code = (string) $obsCod["b:ObservationCodifiee"];
                if(!array_key_exists($code,$this->observationsCodifieesArr)){
                  echo "L'identité  ".  $this->identifiant." possède une observation codifié de code ".$code." non trouvé dans les observations codifiées \n";
                  continue;
                }
                $this->observationsCodifiees[$code] = $this->observationsCodifieesArr[$code];
              }
            }
          }
        }
      }
      if($nameField == "b:LibelleGroupe"){
          $this->groupe = (string) $field;
      }
    }

    private function searchSimpleStringField($nameField, $field,$matchName,$fieldName){
      if($nameField == $matchName){
        if((string) $field){
          $this->$fieldName = (string) $field;
        }
      }
    }

    private function searchSimpleDateField($nameField, $field,$matchName,$fieldName){
      if($nameField == $matchName){
         if(count($field)){
           if(count($field) > 1){ var_dump($nameField,$field); continue; }
         }
         $date = (string) $field;
         if($date != "0001-01-01T00:00:00"){
           $this->$fieldName = (new DateTime($date))->format("Y-m-d");
         }
       }
    }

    private function searchArrayNamedField($nameField, $field,$matchName,$fieldName,$assoc){
      if($nameField == $matchName){
          $arrayMatched = ((array) $field);
          if(count($arrayMatched)){
            foreach ($arrayMatched as $fieldsXml) {
              $obj = new stdClass();
              $fieldsXmlArr = ((array) $fieldsXml);
              foreach ($assoc as $key => $value) {
                if(array_key_exists($key,$fieldsXmlArr)){
                    $obj->$value = (string) $fieldsXmlArr[$key];
                }
              }
              array_push($this->$fieldName,$obj);
            }
          }
       }
    }

    protected function searchCoordonnees($nameField, $field){
          $assoc = array("b:Adresse1" => "adresse1",
                         "b:Adresse2" => "adresse2",
                         "b:Adresse3" => "adresse3",
                         "b:AdresseEtrangere" => "adresseEtrangere",
                         "b:Canton" => "canton",
                         "b:CleCoordonnee" => "cleCoordonnee",
                         "b:Commune" => "commune",
                         "b:CommuneLibelle" => "communeLibelle",
                         "b:LibelleACheminement" => "libelleACheminement",
                         "b:Pays" => "pays");
          $this->searchArrayNamedField($nameField,$field,"b:Coordonnees","coordonnees",$assoc);
    }

    protected function searchCommunications($nameField, $field){
      $assoc = array("b:Email" => "email",
                     "b:Fax" => "fax",
                     "b:NumeroCommunication" => "numeroCommunication",
                     "b:Portable" => "portable",
                     "b:SiteWeb" => "siteweb",
                     "b:Telephone" => "telephone",
                     "b:TypeContact" => "typeContact");
      $this->searchArrayNamedField($nameField,$field,"b:Communications","communications",$assoc);
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

        $coordonnees = $this->coordonnees[0];
        $siege->adresse = $coordonnees->adresse1;
        if($coordonnees->addresse2 || $coordonnees->adresse3){
          $siege->adresse_complementaire = $coordonnees->adresse2.($coordonnees->adresse3)? " ".$coordonnees->addresse3 : '';
        }
        $siege->code_postal = $coordonnees->codePostal;
        $siege->commune = $coordonnees->communeLibelle;
        if($coordonnees->adresseEtrangere){
          $siege->pays = $coordonnees->pays;
        }else{
          $siege->pays = "France";
        }

        $communication = $this->communications[0];

        $societe->telephone = $communication->telephone;
        $societe->email = $communication->email;
        $societe->fax = $communication->fax;

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
      $compte->telephone_mobile = $coordonnees->portable;
      $compte->site_internet = $coordonnees->site_web;
      $compte->fonction = $coordonnees->type_contact;
      $compte->save();

      echo "L'entité $this->identifiant CVI ($this->cvi)  C'est un compte =>  $compte->_id \n";
    }

    public function setTags($c){
      if($this->groupe){
        $c->addTag('manuel',$this->groupe);
      }
      if(count($this->observationsCodifiees)){
      //  echo "mis à jour des observationsCodifiees pour le compte ".  $c->_id." ";
        foreach($this->observationsCodifiees as $obsKey => $obs){
          $tag = $obs.' '.$obsKey;
        //  echo $tag." | ";
          $c->addTag('manuel',$tag);
        }
      //  echo "\n";
    }
  }

}

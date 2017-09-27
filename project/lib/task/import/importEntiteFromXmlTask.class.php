<?php

class importEntiteFromXmlTask extends sfBaseTask
{

    protected $observationsCodifieesArr = array();
    protected $groupeTagsArr = array();
    protected $fonctionsArr = array();

    protected $groupeInterlocuteursArr = array();

    protected $groups = array();
    protected $ref_groups = array();

    protected $identifiant = null;
    protected $cvi = null;
    protected $multipleCvi = array();

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
    protected $groupesTags = array();
    protected $groupeInterlocuteurs = array();

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

        $path_groupes_tags = dirname(__FILE__)."/../../../data/configuration/rhone/groupes_tags.csv";
        $groupeTagsCsv = new CsvFile($path_groupes_tags);

        $path_groupes_interlocuteurs = dirname(__FILE__)."/../../../data/configuration/rhone/groupes_interlocuteurs.csv";
        $groupesInterlocuteursCsv = new CsvFile($path_groupes_interlocuteurs);

        $path_fonctions = dirname(__FILE__)."/../../../data/configuration/rhone/fonctions.csv";
        $fonctionsCsv = new CsvFile($path_fonctions);

        foreach ($observationsCodifieesCsv->getCsv() as $row) {
          $this->observationsCodifieesArr[$row[0]] = $row;
        }

        foreach ($groupeTagsCsv->getCsv() as $row) {
          $this->groupeTagsArr[$row[0]] = $row;
        }

        foreach ($groupesInterlocuteursCsv->getCsv() as $row) {
          $this->groupeInterlocuteursArr[$row[0]] = $row;
        }

        foreach ($fonctionsCsv->getCsv() as $row) {
          $this->fonctionsArr[$row[0]] = $row[1];
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
          $this->searchSimpleStringField($nameField, $field,"b:Siret","siret");

          $this->searchCoordonnees($nameField,$field);
          $this->searchCommunications($nameField,$field);
          $this->searchObservationsCodifiees($nameField,$field);
          $this->searchRefGroupsProfil($nameField,$field);
        }
        
        if($this->cvi  || count($this->multipleCvi) || $this->siret){
            $this->importSociete();
        }else{
          $this->importInterlocuteur();
        }

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


  public function searchCvi($nameField, $field){
            if($nameField == "b:Evv" && boolval((string) $field)){
              $evvStr = (string) $field;
              $pattern = array("/^,/","/,$/","/[,]{2,}/");
              $replace = array("","",",");
              $evvStrPurged = preg_replace($pattern,$replace,$evvStr);
              $evvArray = explode(',',$evvStrPurged);
              if(count($evvArray) > 1 && (count(array_unique($evvArray)) > 1)){
                echo "l'identité  ".  $this->identifiant." a des cvis différents : ".$evvStrPurged." plusieurs établissements\n";
                $this->multipleCvi = array_unique($evvArray);
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

    protected function searchRefGroupsProfil($nameField, $field){
            if($nameField == "b:Profils"){
              $profilesArray = ((array) $field);
              foreach ($profilesArray as $key => $identitesProfils) {
                $identitesProfilsArray = (array) $identitesProfils;
                $refKeyGroup = $identitesProfilsArray["b:CleGroupe"];
                $fonction = $identiteProfilArr["b:Fonction"];
                if(array_key_exists($refKeyGroup,$this->groupeTagsArr)){
                  $this->groupesTags[] = $this->groupeTagsArr[$refKeyGroup][1]." ".$this->groupeTagsArr[$refKeyGroup][2];
                  continue;
                  }
                if(array_key_exists($refKeyGroup,$this->groupeInterlocuteursArr)){
                  $this->groupeInterlocuteurs[] = array_merge($this->groupeInterlocuteursArr[$refKeyGroup],array($fonction));
                }
                foreach ($identitesProfilsArray as $key => $identiteProfilsArray) {
                  $identiteProfilArr = (array) $identiteProfilsArray;
                  $refKeyGroup = $identiteProfilArr["b:CleGroupe"];
                  $fonction = $identiteProfilArr["b:Fonction"];
                  if(array_key_exists($refKeyGroup,$this->groupeTagsArr)){
                    $this->groupesTags[] = $this->groupeTagsArr[$refKeyGroup][1]." ".$this->groupeTagsArr[$refKeyGroup][2];
                    continue;
                    }
                  if(array_key_exists($refKeyGroup,$this->groupeInterlocuteursArr)){
                    $this->groupeInterlocuteurs[] = array_merge($this->groupeInterlocuteursArr[$refKeyGroup],array($fonction));
                  }
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
              if(!array_key_exists($code,$this->observationsCodifieesArr) || !$this->observationsCodifieesArr[$code][2]){
                //echo "L'identité  ".  $this->identifiant." possède une observation codifié de code ".$code." non trouvé dans les observations codifiées \n";
                continue;
              }
              $this->observationsCodifiees[$code] = $this->observationsCodifieesArr[$code][2];
            }elseif((get_class($obsCodifie) == 'SimpleXMLElement') && count($obsCodifie)){
              $obsCod =(array) $obsCodifie;
              if(array_key_exists("b:ObservationCodifiee",$obsCod)){
                $code = (string) $obsCod["b:ObservationCodifiee"];
                if(!array_key_exists($code,$this->observationsCodifieesArr) || !$this->observationsCodifieesArr[$code][2]){
                  //echo "L'identité  ".  $this->identifiant." possède une observation codifié de code ".$code." non trouvé dans les observations codifiées \n";
                  continue;
                }
                $this->observationsCodifiees[$code] = $this->observationsCodifieesArr[$code][2];
              }
            }
          }
        }
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
           if(count($field) > 1){ echo "Champ ".$nameField." est multiple : ".implode(",",$field); return; }
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
                         "b:CodePostal" => "codePostal",
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

    protected function importSociete(){
        if(!$this->identifiant){
          echo "Le fichier xml $file_path n'a pas d'identifiant!\n"; exit;
        }
        $societe = new societe();
        $societe->identifiant = sprintf("%06d",$this->identifiant);
        if($this->cvi || count($this->multipleCvi)){
          $societe->type_societe = SocieteClient::TYPE_OPERATEUR ;
        }else{
          $societe->type_societe =  "AUTRE" ;
        }

        if(count($this->groupeInterlocuteurs)){
          foreach ($this->groupeInterlocuteurs as $key => $interloc) {
            echo "/!\ Un profil trouvé : ".$this->identifiant." est aussi contact de la societe : ".$interloc[2]."  [".implode(",",$interloc)."] => importé quand même\n";
          }
        }

        $societe->constructId();
        $societe->raison_sociale = $this->buildRaisonSociete();
        $societe->add('date_modification', $this->date_modification);
        $societe->add('date_creation', $this->date_creation);
        $societe->code_comptable_client = $societe->identifiant;
        $siege = $societe->getOrAdd('siege');

        $coordonnees = $this->coordonnees[0];
        $siege->adresse = $coordonnees->adresse1;
        if($coordonnees->adresse2){
          $siege->adresse_complementaire = $coordonnees->adresse2;
        }
        if($coordonnees->adresse3){
          $siege->adresse_complementaire .= " ".$coordonnees->addresse3;
        }
        $siege->code_postal = $coordonnees->codePostal;
        $siege->commune = $coordonnees->communeLibelle;
        if(!$coordonnees->adresseEtrangere || ($coordonnees->adresseEtrangere == "false")){
          $siege->pays = "France";
        }else{
          $siege->pays = $coordonnees->pays;
        }

        $communication = $this->communications[0];

        $societe->telephone = ($communication->telephone && $communication->telephone != "__.__.__.__.__")? $communication->telephone : "";
        $societe->fax = ($communication->fax && $communication->fax != "__.__.__.__.__")? $communication->fax : "";
        $societe->email = $communication->email;

        $societe->siret = $this->siret;
        if($this->date_archivage){
          $societe->setStatut(SocieteClient::STATUT_SUSPENDU);
        }
        $societe->save();

        $societe = SocieteClient::getInstance()->find($societe->_id);
        if($this->multipleCvi){
          foreach ($this->multipleCvi as $cvi) {
            $etablissement = $societe->createEtablissement($this->type_etablissement);
            // $etablissement->setCompte($societe->getMasterCompte()->_id);
            $etablissement->constructId();
            $etablissement->cvi = $cvi;
            $etablissement->nom = $this->buildRaisonSociete();
            $etablissement->save();
          }
        }elseif($this->cvi){
        $etablissement = $societe->createEtablissement($this->type_etablissement);
        // $etablissement->setCompte($societe->getMasterCompte()->_id);
        $etablissement->constructId();
        $etablissement->cvi = $this->cvi;
        $etablissement->nom = $this->buildRaisonSociete();
        $etablissement->save();
      }

      $compte = $societe->getMasterCompte();
      $compte->nom = $this->buildRaisonSociete();
      $compte->updateNomAAfficher();
      $compte->telephone_mobile = $coordonnees->portable;
      $compte->site_internet = $coordonnees->site_web;
      $compte->fonction = $coordonnees->type_contact;
      $this->setTags($compte);
      $compte->save();

      echo "L'entité $this->identifiant CVI ($this->cvi)  Compte =>  $compte->_id \n";
    }

    protected function importInterlocuteur(){
      if(count($this->groupeInterlocuteurs)){
        foreach ($this->groupeInterlocuteurs as $key => $interloc) {
          echo "INTERLOCUTEUR profil trouvé [".implode(",",$interloc)."] : ".$this->identifiant." est contact de la societe : ".$interloc[1]."\n";
          $identifiantSoc = sprintf("%06d",$interloc[1]);
          $societe = SocieteClient::getInstance()->find($identifiantSoc);
          if(!$societe){
            echo "La société $identifiantSoc n'est pas dans la base\n";
          }else{
            $compte = CompteClient::getInstance()->createCompteFromSociete($societe);

            $compte->prenom = $this->prenom;
            $compte->nom = $this->nom;

            $compte->fonction = (array_key_exists($interloc[7],$this->fonctionsArr))? $this->fonctionsArr[$interloc[7]] : $interloc[7];


            $coordonnees = $this->coordonnees[0];

            $compte->adresse = $coordonnees->adresse1;

            if($coordonnees->adresse2){
              $compte->adresse_complementaire = $coordonnees->adresse2;
            }
            if($coordonnees->adresse3){
              $compte->adresse_complementaire .= " ".$coordonnees->addresse3;
            }

            $compte->code_postal = $coordonnees->codePostal;

            if(!$coordonnees->adresseEtrangere || ($coordonnees->adresseEtrangere == "false")){
              $compte->pays = "France";
            }else{
              $compte->pays = $coordonnees->pays;
            }

            $compte->commune = $coordonnees->communeLibelle;

            $communication = $this->communications[0];

            $compte->telephone_bureau = ($communication->telephone && $communication->telephone != "__.__.__.__.__")? $communication->telephone : "";
            $compte->telephone_mobile = ($communication->portable && $communication->portable != "__.__.__.__.__")? $coordonnees->portable : "";
            $compte->fax = ($communication->fax && $communication->fax != "__.__.__.__.__")? $communication->fax : "";
            $compte->email = $communication->email;
            $compte->site_internet = $coordonnees->site_web;
            $this->setTags($compte);
            $compte->save();
            echo "La société $identifiantSoc a un nouvel interlocuteur : $compte->nom \n";
          }
        }
      }
    }

    public function setTags($c){
      if(count($this->observationsCodifiees)){
        echo "OBS Codifiees ".implode(",",$this->observationsCodifiees)." ". $c->_id." \n";
        foreach($this->observationsCodifiees as $obsKey => $obs){
          $tag = 'OBS '.$obs;
          $c->addTag('manuel',$tag);
        }
    }
    if(count($this->groupesTags)){
      echo "GROUPE Tags ". implode(",",$this->groupesTags) ." ".$c->_id." \n";
      foreach($this->groupesTags as $grpKey => $grp){
         $c->addTag('manuel',"GRP ".KeyInflector::unaccent(str_replace(array(")","("),array('',''),$grp)));
       }
    }

  }

}

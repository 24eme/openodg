<?php

class importEntiteFromXmlTask extends sfBaseTask
{
    const COORD_ADRESSE_1 = 0;
    const COORD_ADRESSE_2 = 1;
    const COORD_ADRESSE_3 = 2;
    const COORD_ADRESSE_ETRANGERE = 3;
    const COORD_CANTON = 4;
    const COORD_CODEPOSTAL = 5;
    const COORD_CLE = 6;
    const COORD_COMMUNE = 7;
    const COORD_COMMUNELIBELLE = 8;
    const COORD_LIBELLEACHEMINEMEN = 9;
    const COORD_PAYS = 10;

    const COM_EMAIL = 0;
    const COM_FAX = 1;
    const COM_NUM = 2;
    const COM_PORTABLE = 3;
    const COM_SITEWEB = 4;
    const COM_TEL = 5;
    const COM_TYPECONTACT = 6;

    const TYPE_SOC_MORALE = "M";
    const TYPE_SOC_PHYSIQUE = "P";

    const FAMILLE_NVINI = "NVINI";
    const FAMILLE_CCAIR = "CCAIR";
    const FAMILLE_CCCDR = "CCCDR";

    protected $arrayXML = array();

    protected $observationsCodifieesArr = array();
    protected $groupeTagsArr = array();
    protected $fonctionsArr = array();
    protected $groupeInterlocuteursArr = array();



    protected static $coordonneesKeys = array(self::COORD_ADRESSE_1 => "b:Adresse1",
                                    self::COORD_ADRESSE_2 => "b:Adresse2",
                                    self::COORD_ADRESSE_3 => "b:Adresse3",
                                    self::COORD_ADRESSE_ETRANGERE => "b:AdresseEtrangere",
                                    self::COORD_CANTON => "b:Canton",
                                    self::COORD_CODEPOSTAL => "b:CodePostal",
                                    self::COORD_CLE => "b:CleCoordonnee",
                                    self::COORD_COMMUNE => "b:Commune",
                                    self::COORD_COMMUNELIBELLE => "b:CommuneLibelle",
                                    self::COORD_LIBELLEACHEMINEMEN => "b:LibelleACheminement",
                                    self::COORD_PAYS => "b:Pays");

    protected static $communicationsKeys = array(self::COM_EMAIL => "b:Email",
                                    self::COM_FAX => "b:Fax",
                                    self::COM_NUM => "b:NumeroCommunication",
                                    self::COM_PORTABLE => "b:Portable",
                                    self::COM_SITEWEB => "b:SiteWeb",
                                    self::COM_TEL => "b:Telephone",
                                    self::COM_TYPECONTACT => "b:TypeContact");

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
        $this->initLoad($file_path);
        $this->loadMatchFiles();

        $this->import();

    }

    protected function import(){
      if(!isset($this->arrayXML["b:CleIdentite"]) || !$this->arrayXML["b:CleIdentite"]){
        echo "Le fichier xml $file_path n'a pas d'identifiant!\n"; exit;
      }
      $identifiant = $this->arrayXML["b:CleIdentite"];
      $cvis = $this->getCvis($identifiant);
      $siret = $this->arrayXML["b:Siret"];
      $ppm = $this->arrayXML["b:NumPPM"];
      if(is_array($siret) && !count($siret)){
        $siret = "";
      }
      if(is_array($ppm)){
        $ppm = "";
      }
      try{
        if(count($cvis) || $siret || $ppm){
            $this->importSociete($identifiant,$cvis,$siret,$ppm);
        }else{
          $groupsProfil = $this->getRefsGroupsProfil();
          if(count($groupsProfil)){
            $this->importAsInterlocuteurOrSociete($identifiant);
          }else{
            $this->importSociete($identifiant,$cvis,$siret,$ppm);
          }
        }
      }catch(sfException $e){
        echo "Problème avec ".$identifiant."\n";
        echo $e;

      }
    }


    protected function importSociete($identifiant,$cvis,$siret,$ppm){

            $societeIdentifiant = sprintf("%06d",$identifiant);
            $soc = SocieteClient::getInstance()->find($societeIdentifiant);
            if($soc){
              $soc->delete();
            }

            $societe = new societe();
            $societe->identifiant = $societeIdentifiant;
            if($cvis){
              $societe->type_societe = SocieteClient::TYPE_OPERATEUR ;
            }else{
              $societe->type_societe =  SocieteClient::TYPE_AUTRE ;
            }
            $groupeInterlocuteurs = $this->getRefsGroupsProfil();
            if(count($groupeInterlocuteurs)){
              foreach ($groupeInterlocuteurs as $key => $interloc) {
                echo "/!\ Un profil trouvé : ".$identifiant." est aussi contact de la societe : ".$interloc[2]."  [".implode(",",$interloc)."] => importé quand même\n";
              }
            }
            $societe->constructId();
            $societe->raison_sociale = $this->buildRaisonSociete();

            if($dateModification = $this->getSimpleDateField($this->arrayXML , 'b:DateModification')){
              $societe->add('date_modification', $dateModification);
            }
            if($dateCreation = $this->getSimpleDateField($this->arrayXML, 'b:DateCreation')){
              $societe->add('date_creation', $dateCreation);
            }

            $societe->code_comptable_client = $societe->identifiant;
            $siege = $societe->getOrAdd('siege');

            $societe->siret = $siret;


            $societeCoordonnees = $this->getCoordonneesInArr($this->arrayXML['b:Coordonnees']['b:Identite_Coordonnee']);

            $this->updateDocOrFieldWithCoordonnees($societe->siege,$societeCoordonnees);

            $societeCommunication = $this->getCommunicationsInArr($this->arrayXML['b:Communications']['b:Identite_Communication'],$identifiant);

            $societe->telephone = $societeCommunication[self::COM_TEL];
            $societe->fax = $societeCommunication[self::COM_FAX];
            $societe->email = $societeCommunication[self::COM_EMAIL];

            $dateArchivage = $this->getSimpleDateField($this->arrayXML, 'b:DateArchivage');
            if($dateArchivage){
              $societe->setStatut(SocieteClient::STATUT_SUSPENDU);
            }
            $societe->save();
            $societe = SocieteClient::getInstance()->find($societe->_id);

            $type_etablissement = EtablissementFamilles::FAMILLE_PRODUCTEUR;
            $observationsCodifiees = $this->extractObservationsCodifiees();
            if(array_key_exists(self::FAMILLE_NVINI,$observationsCodifiees)){
              $type_etablissement = EtablissementFamilles::FAMILLE_NEGOCIANT_VINIFICATEUR;
            }
            elseif(array_key_exists(self::FAMILLE_CCAIR,$observationsCodifiees)){
              $type_etablissement = EtablissementFamilles::FAMILLE_COOPERATIVE;
            }
            elseif(array_key_exists(self::FAMILLE_CCCDR,$observationsCodifiees)){
              $type_etablissement = EtablissementFamilles::FAMILLE_COOPERATIVE;
            }

            if(count($cvis) > 1){
              foreach ($cvis as $cvi) {
                $etablissement = $societe->createEtablissement($type_etablissement);
                $etablissement->constructId();
                $etablissement->cvi = $cvi;
                $etablissement->ppm = $ppm;
                $etablissement->nom = $this->buildRaisonSociete();
                $etablissement->save();
              }
            }elseif($cvis){
            $etablissement = $societe->createEtablissement($type_etablissement);
            $etablissement->constructId();
            $etablissement->cvi = array_shift(array_values($cvis));
            $etablissement->ppm = $ppm;
            $etablissement->nom = $this->buildRaisonSociete();
            $etablissement->save();
          }elseif($ppm){
            $etablissement = $societe->createEtablissement($type_etablissement);
            $etablissement->constructId();
            $etablissement->ppm = $ppm;
            $etablissement->nom = $this->buildRaisonSociete();
            $etablissement->save();
          }

          $compte = $societe->getMasterCompte();
          $compte->nom = $this->buildRaisonSociete();
          $compte->updateNomAAfficher();
          $compte->email = $societeCommunication[self::COM_EMAIL];
          $compte->telephone_mobile = $societeCommunication[self::COM_PORTABLE];
          //$compte->telephone_perso = $societeCommunication[self::COM_TEL];
          $compte->site_internet = $societeCommunication[self::COM_SITEWEB];
          $compte->fonction = "";
          $this->setTags($compte);
          $compte->save();

          echo "L'entité $identifiant CVI (".implode(",",$cvis).")  Compte =>  $compte->_id \n";

        }

        protected function importAsInterlocuteurOrSociete($identifiant){
          $groupeInterlocuteurs = $this->getRefsGroupsProfil();
          $typeSociete = $this->getTypeSociete();
          if($typeSociete == self::TYPE_SOC_PHYSIQUE){
              if(count($groupeInterlocuteurs)){
              foreach ($groupeInterlocuteurs as $key => $interloc) {
                echo "INTERLOCUTEUR profil trouvé [".implode(",",$interloc)."] : ".$identifiant." est contact de la societe : ".$interloc[1]."\n";
                $identifiantSoc = sprintf("%06d",$interloc[1]);
                $societe = SocieteClient::getInstance()->find($identifiantSoc);
                if(!$societe){
                  echo "La société $identifiantSoc n'est pas dans la base\n";
                }else{
                  $compte = CompteClient::getInstance()->createCompteFromSociete($societe);

                  $societeCommunication = $this->getCommunicationsInArr($this->arrayXML['b:Communications']['b:Identite_Communication'],$identifiant);

                  $compte->nom = $this->arrayXML['b:RaisonSociale'];
                  $compte->prenom = $this->arrayXML['b:Prenom'];
                  $compte->fonction = (array_key_exists($interloc[7],$this->fonctionsArr))? $this->fonctionsArr[$interloc[7]] : $interloc[7];

                  $interlocCoordonnees = $this->getCoordonneesInArr($interloc["b:Identite_Coordonnee"]);

                  $this->updateDocOrFieldWithCoordonnees($compte,$interlocCoordonnees);

                  $interlocCommunication = $this->getCommunicationsInArr($interloc["b:Identite_Communication"]);

                  $compte->email = $interlocCommunication[self::COM_EMAIL];
                  $compte->telephone_mobile = $interlocCommunication[self::COM_PORTABLE];
                  $compte->site_internet = $interlocCommunication[self::COM_SITEWEB];
                  $this->setTags($compte);
                  $compte->save();
                  echo "La société $identifiantSoc a un nouvel interlocuteur : $compte->nom \n";

                }
              }
            }
          }elseif($typeSociete == self::TYPE_SOC_MORALE){
            echo "Import de la société autre $identifiant    ";
            $this->importSociete($identifiant,array(),"");
          }else{
            echo "/!\ La société $identifiant a pour type :$typeSociete \n";
          }
        }

    protected function updateDocOrFieldWithCoordonnees($doc_or_field,$coordsArr){
      $doc_or_field->adresse = $coordsArr[self::COORD_ADRESSE_1];
      if($coordsArr[self::COORD_ADRESSE_2]){
        $doc_or_field->adresse_complementaire = $coordsArr[self::COORD_ADRESSE_2];
      }
      if($societeCoordonnees[self::COORD_ADRESSE_3]){
        $doc_or_field->adresse_complementaire .= " ".$coordsArr[self::COORD_ADRESSE_3];
      }
      $doc_or_field->code_postal = $coordsArr[self::COORD_CODEPOSTAL];
      $doc_or_field->commune = $coordsArr[self::COORD_COMMUNELIBELLE];
      if(!$coordsArr[self::COORD_ADRESSE_ETRANGERE] || ($coordsArr[self::COORD_ADRESSE_ETRANGERE] == "false")){
        $doc_or_field->pays = "France";
      }else{
        $doc_or_field->pays = $coordsArr[self::COORD_PAYS];
      }
      return $doc_or_field;
    }

    protected function initLoad($path){
        $xml_content_str = file_get_contents($path);
        $xml = simplexml_load_string($xml_content_str, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $this->arrayXML = json_decode($json,TRUE);
        if(!$this->arrayXML || !count($this->arrayXML)){
          echo "L'xml de path $path n'a pas été intégré\n";
        }
    }

    protected function loadMatchFiles(){
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
    }

    public function getTypeSociete(){
      if(isset($this->arrayXML["b:Type"])){
        return $this->arrayXML["b:Type"];
      }
      return null;
    }

    protected function getCvis($identifiant){
            $cviArr = array();
            if(isset($this->arrayXML["b:Evv"]) && boolval((string) $this->arrayXML["b:Evv"])){
              if(is_array($this->arrayXML["b:Evv"]) && !count($this->arrayXML["b:Evv"])){
                return array();
              }
              $evvStr = (string) $this->arrayXML["b:Evv"];
              $pattern = array("/^,/","/,$/","/[,]{2,}/");
              $replace = array("","",",");
              $evvStrPurged = preg_replace($pattern,$replace,$evvStr);
              $evvArray = explode(',',$evvStrPurged);
              if(count($evvArray) > 1 && (count(array_unique($evvArray)) > 1)){
                echo "l'identité  ".  $identifiant." a des cvis différents : ".$evvStrPurged." plusieurs établissements\n";
                $cviArr = array_unique($evvArray);
              }else{
                  foreach ($evvArray as $cvi_c) {
                      $cviArr[$cvi_c] = $cvi_c;
                  }
                }
            }
            return $cviArr;
    }

    protected function buildRaisonSociete(){
      $civilite = $this->arrayXML['b:Titre'];
      $prenom = $this->arrayXML['b:Prenom'];
      $nom = $this->arrayXML['b:RaisonSociale'];

      $raison_sociale = $nom;
      $raison_sociale .= ($prenom)? " ".$prenom    : "";
      return $raison_sociale;
    }


    protected function getRefsGroupsProfil(){
      $groupeInterlocuteurs = array();
      if(isset($this->arrayXML['b:Profils']) && count($this->arrayXML['b:Profils'])){
        foreach ($this->arrayXML['b:Profils'] as $key => $identitesProfils) {
          if(isset($identitesProfils["b:CleGroupe"])){
            $refKeyGroup = $identitesProfils["b:CleGroupe"];
            $fonction = $identitesProfils["b:Fonction"];
            if(array_key_exists($refKeyGroup,$this->groupeInterlocuteursArr)){
              $groupeInterlocuteurs[] = array_merge($this->groupeInterlocuteursArr[$refKeyGroup],array($fonction),$identitesProfils);
            }
          }else{
            foreach ($identitesProfils as $identiteProfil) {
              $refKeyGroup = $identiteProfil["b:CleGroupe"];
              $fonction = $identiteProfil["b:Fonction"];
              if(array_key_exists($refKeyGroup,$this->groupeInterlocuteursArr)){
                $groupeInterlocuteurs[] = array_merge($this->groupeInterlocuteursArr[$refKeyGroup],array($fonction),$identiteProfil);
              }
            }
          }
      }
    }
      return $groupeInterlocuteurs;
    }

    protected function getTagsArrayFromProfil(){
      $groupesTags = array();
      if(isset($this->arrayXML['b:Profils']) && count($this->arrayXML['b:Profils'])){
        foreach ($this->arrayXML['b:Profils'] as $key => $identitesProfils) {
          if(isset($identitesProfils["b:CleGroupe"])){
            $this->buildGroupesTags($groupesTags,$identitesProfils);
          }else{
            if(is_array($identitesProfils)){
              foreach ($identitesProfils as $identitesProfil) {
                $refKeyGroup = (isset($identitesProfil["b:CleGroupe"]))? $identitesProfil : null;
                if($refKeyGroup){
                  $this->buildGroupesTags($groupesTags,$identitesProfil);
                }
              }
            }
          }
        }
      }
      return $groupesTags;
    }

    private function buildGroupesTags(&$groupesTags,$identiteProfil){
      $refKeyGroup = $identiteProfil["b:CleGroupe"];
      if(!array_key_exists($refKeyGroup,$this->groupeTagsArr)){
        return;
      }
      $groupesTags[] = array_merge(array($this->groupeTagsArr[$refKeyGroup][1]." ".$this->groupeTagsArr[$refKeyGroup][2]),$identiteProfil);
      return $groupesTags;
    }

    private function getSimpleDateField($arr, $fieldName){
      if(array_key_exists($fieldName,$arr)){
         if(is_array($arr[$fieldName]) && count($arr[$fieldName])){
           if(count($arr[$fieldName]) > 1){ echo "Champ ".$fieldName." est multiple : ".implode(",",$arr); return; }
         }
         $date = (string) $arr[$fieldName];
         if($date != "0001-01-01T00:00:00"){
           return (new DateTime($date))->format("Y-m-d");
         }
       }
       return null;
    }

    protected function getCoordonneesInArr($arr){
          $coordonnees = array();
          foreach (self::$coordonneesKeys as $k => $keyName) {
            if(array_key_exists($keyName,$arr)){
              $coordonnees[$k] = $arr[$keyName];
              if(is_array($arr[$keyName])){
                $coordonnees[$k] = "";
              }
            }
          }
          return $coordonnees;
    }

    protected function getCommunicationsInArr($arr, $identifiant){
      $communications = array();
      if(isset($arr['b:CleCommunication'])){
        $this->buildCommunicationArr($arr,$communications);
      }else{
        echo "$identifiant : Clé communication multiple\n";
        foreach ($arr as $key => $communicationArr) {
          if(isset($communicationArr['b:CleCommunication'])){
            $this->buildCommunicationArr($communicationArr,$communications);
            break;
          }
        }
      }
      return $communications;
    }

    private function buildCommunicationArr($arr,&$communications){
      foreach (self::$communicationsKeys as $k => $keyName) {
        if(array_key_exists($keyName,$arr)){
          if(is_array($arr[$keyName])){
            $communications[$k] = explode(",",$arr[$keyName]);
          }else{
            $communications[$k] = ($arr[$keyName] !== '__.__.__.__.__')? $arr[$keyName] : "";
          }
        }
      }
    }

    public function setTags($c){
      $this->addObservationsCodifiees($c);

      $this->addGroupesTags($c);

    }

    public function extractObservationsCodifiees(){
      $observationsCodifiees = array();
      if(isset($this->arrayXML["b:ObservationCodifiee"]) && count($this->arrayXML["b:ObservationCodifiee"])){
        $observationsCodifieesArray = $this->arrayXML["b:ObservationCodifiee"];
        if(array_key_exists("b:Identite_ObservationCodifiee",$observationsCodifieesArray)){
          foreach ($observationsCodifieesArray["b:Identite_ObservationCodifiee"] as $obsCodifie) {
            if(is_string($obsCodifie)){
              if(!array_key_exists($obsCodifie,$this->observationsCodifieesArr) || !$this->observationsCodifieesArr[$obsCodifie][2]){
                //echo "L'identité  ".  $c->identifiant." possède une observation codifié de code ".$obsCodifie." non trouvée dans les observations codifiées \n";
                continue;
              }
              $observationsCodifiees[$obsCodifie] = $this->observationsCodifieesArr[$obsCodifie];
            }else{
              if(array_key_exists("b:ObservationCodifiee",$obsCodifie)){
                $code = $obsCodifie["b:ObservationCodifiee"];
                if(!array_key_exists($code,$this->observationsCodifieesArr) || !$this->observationsCodifieesArr[$code][2]){
                  //echo "L'identité  ".  $this->identifiant." possède une observation codifié de code ".$code." non trouvé dans les observations codifiées \n";
                  continue;
                }
                $observationsCodifiees[$code] = $this->observationsCodifieesArr[$code];
              }
            }
          }
        }
      }
      return $observationsCodifiees;
    }

    protected function addObservationsCodifiees($c){
      $observationsCodifiees = $this->extractObservationsCodifiees();
      if(count($observationsCodifiees)){
        echo "OBS Codifiees ";
        foreach($observationsCodifiees as $obsKey => $obs){
          echo implode(",",$obs)."   -   ";
          $tag = ''.$obs[2];
          $c->addTag('manuel',$tag);
          if($obs[3]){
            $c->setStatut(SocieteClient::STATUT_SUSPENDU);
            if($c->compte_type == "SOCIETE"){
                foreach($c->getOrigines() as $origine) {
                  $soc = SocieteClient::getInstance()->find($origine);
                  $soc->setStatut(SocieteClient::STATUT_SUSPENDU);
                  $soc->save();
                }
              }
              foreach ($c->getSociete()->getEtablissementsObj() as $etablissement) {
                # code...
                $etb = $etablissement->etablissement;
                $etb->setStatut(SocieteClient::STATUT_SUSPENDU);
                $etb->save();
              }
            }
          }
          echo $c->_id." \n";
        }
    }

    protected function addGroupesTags($c){
      $groupesTags = $this->getTagsArrayFromProfil();
      if(count($groupesTags)){
        echo "Association au groupe ". implode(",",$groupesTags) ." ".$c->_id." \n";
        foreach($groupesTags as $grpKey => $grp){
          $fonction = (array_key_exists($grp["b:Fonction"],$this->fonctionsArr))? $this->fonctionsArr[$grp["b:Fonction"]] : $grp["b:Fonction"];
          $c->addInGroupes($grp[0],$fonction);
         }
      }
    }

}

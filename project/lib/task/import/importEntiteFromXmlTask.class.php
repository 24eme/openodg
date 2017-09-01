<?php

class importEntiteFromXmlTask extends sfBaseTask
{


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

        $societe = new societe();

        $identifiant  = null;
        $cvi = null;


        $nom = null;
        $prenom = null;
        $civilite = null;

        $adresse1 = null;
        $adresse2 = null;
        $adresse2 = null;
        $adresseEtrangère = null;
        $canton = null;
        $cleCoordonnee = null;
        $canton = null;
        $codePostal = null;
        $commune = null;
        $communeLibelle = null;
        $libelleACheminement = null;
        $pays = null;

        $observationsCodifiees = array();
        foreach ($xmlEntite as $nameField => $field) {
            if($nameField == "b:CleIdentite"){
              if(count($field)){
                if(count($field) > 1){ var_dump($nameField,$field); continue; }
              }
              $identifiant = (string) $field;
            }
            if($nameField == "b:Evv" && boolval((string) $field)){
              $cvi = (string) $field;
            }
            if($nameField == "b:Prenom"){
              $prenom = (string) $field;
            }
            if($nameField == "b:RaisonSociale"){
              $nom = (string) $field;
            }
            if($nameField == "b:Titre"){
              $civilite = (string) $field;
            }

            if($nameField == "b:ObservationCodifiee"){
              $observationsCodifieesArray = ((array) $field);
              if(array_key_exists("b:Identite_ObservationCodifiee",$observationsCodifieesArray)){
                foreach ($observationsCodifieesArray["b:Identite_ObservationCodifiee"] as $obsCodifie) {
                  if(boolval((string) $obsCodifie)){ $observationsCodifiees[] = (string) $obsCodifie; }
                }
              }
            }
            if($nameField == "b:Coordonnees"){
              $coordonneesArray = ((array) $field);
              if(array_key_exists("b:Identite_Coordonnee",$coordonneesArray)){
                $coords = (array) $coordonneesArray["b:Identite_Coordonnee"];
                if(array_key_exists("b:Adresse1",$coords)){
                  $adresse1 = (string) $coords["b:Adresse1"];
                  $adresse2 = (string) $coords["b:Adresse2"];
                  $adresse2 = (string) $coords["b:Adresse3"];
                  $adresseEtrangere = filter_var(((string) $coords["b:AdresseEtrangere"]), FILTER_VALIDATE_BOOLEAN);
                  $canton = (string) $coords["b:Canton"];
                  $cleCoordonnee = (string) $coords["b:CleCoordonnee"];
                  $canton = (string) $coords["b:Canton"];
                  $codePostal = (string) $coords["b:CodePostal"];
                  $commune = (string) $coords["b:Commune"];
                  $communeLibelle = (string) $coords["b:CommuneLibelle"];
                  $libelleACheminement = (string) $coords["b:LibelleACheminement"];
                  $pays = (string) $coords["b:Pays"];
                }
              }
            }
        }

        if(!$identifiant){
          echo "Le fichier xml $file_path n'a pas d'identifiant!\n"; exit;
        }
        $societe->identifiant = sprintf("%06d",$identifiant);
        if(!$cvi){
            echo "L'entité $identifiant n'a pas de CVI!\n"; exit;
        }

          $societe->constructId();

          $societe->raison_sociale = ($civilite)? $civilite." " : "";
          $societe->raison_sociale = ($prenom)? $prenom." " : "";;
          $societe->raison_sociale = $nom;

          $siege = $societe->getOrAdd('siege');
          $siege->adresse = $adresse1;
          if($addresse2 || $adresse3){
            $siege->adresse_complementaire = $adresse2.($adresse3)? " ".$addresse3 : '';
          }
          $siege->code_postal = $codePostal;
          $siege->commune = $commune;
          if($adresseEtrangere){
            $siege->pays = $pays;
          }else{
            $siege->pays = "France";
          }
          $societe->save();

          $societe = SocieteClient::getInstance()->find($societe->_id);

          $etablissement = $societe->createEtablissement('PRODUCTEUR');
          // $etablissement->setCompte($societe->getMasterCompte()->_id);
          $etablissement->constructId();
          $etablissement->cvi = $cvi;
          if(count($observationsCodifiees)){
            echo "mis à jour des observationsCodifiees pour le compte ".  $etablissement->getMasterCompte()->_id." ";
            foreach($observationsCodifiees as $obs){
              echo $obs." | ";
              $etablissement->getMasterCompte()->addTag('observationsCodifiees',$obs);
            }
            echo "\n";
          }
          $etablissement->nom = ($civilite)? $civilite." " : "";
          $etablissement->nom = ($prenom)? $prenom." " : "";;
          $etablissement->nom = $nom;

          $etablissement->save();
    }



}

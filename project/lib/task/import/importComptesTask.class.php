<?php

class importComptesTask extends sfBaseTask
{

    const CSV_ID                    = 0;
    const CSV_TYPE_LIGNE            = 1;
    const CSV_CVI                   = 2;
    const CSV_SIREN                 = 3;
    const CSV_SIRET                 = 4;
    const CSV_TVA_INTRA             = 5;
    const CSV_CIVILITE              = 6;
    const CSV_RAISON_SOCIALE        = 7;
    const CSV_NOM                   = 8;
    const CSV_PRENOM                = 9;
    const CSV_FAMILLE               = 10;
    const CSV_ADRESSE_1             = 11;
    const CSV_ADRESSE_2             = 12;
    const CSV_ADRESSE_3             = 13;
    const CSV_CODE_POSTAL           = 14;
    const CSV_COMMUNE               = 15;
    const CSV_CODE_INSEE            = 16;
    const CSV_CEDEX                 = 17;
    const CSV_PAYS                  = 18;
    const CSV_TEL                   = 19;
    const CSV_FAX                   = 20;
    const CSV_PORTABLE              = 21;
    const CSV_EMAIL                 = 22;
    const CSV_WEB                   = 23;
    const CSV_ATTRIBUTS             = 24;
    const CSV_DATE_ARCHIVAGE        = 25;
    const CSV_DATE_CREATION         = 26;
    const CSV_LIAISON               = 27;
    const CSV_LIAISON_NOM           = 28;

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('file', sfCommandArgument::REQUIRED, "Fichier csv pour l'import"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'Comptes';
        $this->briefDescription = 'Import des comptes';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $datas = array();
        $id = null;
        foreach(file($arguments['file']) as $line) {
            $line = str_replace("\n", "", $line);

            if(preg_match("/^000000#/", $line)) {
                
                continue;
            }

            $data = str_getcsv($line, ';');

            if(!$id && $data[self::CSV_TYPE_LIGNE] != "1.COMPTE") {
                continue;
            }

            if($id && $id != $data[self::CSV_ID]) {
                $this->importLines($datas);
                $id = null;
                $datas = array();
            }

            $id = $data[self::CSV_ID];

            $datas[] = $data;

        }

        if($id) {
            $this->importLines($datas);
        }
    }

    protected function save($compte, $etablissement) {

        $compte->constructId();
        if($compte->isNew()) {
            echo sprintf("SUCCESS;%s;%s\n", "Création", $compte->_id);
        } else {
            echo sprintf("SUCCESS;%s;%s\n", "Mise à jour", $compte->_id);
        }
        $compte_existant = acCouchdbManager::getClient()->find($compte->_id, acCouchdbClient::HYDRATE_JSON);
        if($compte_existant) {
            acCouchdbManager::getClient()->deleteDoc($compte_existant);
        }
        $compte->save(false);

        if($etablissement) {
            $etablissement->constructId();
            $etablissement->synchroFromCompte($compte);
            if($etablissement->isNew()) {
                echo sprintf("SUCCESS;%s;%s\n", "Création", $etablissement->_id);
            } else {
                echo sprintf("SUCCESS;%s;%s\n", "Mise à jour", $etablissement->_id);
            }
            $etablissement->save();
        }   
    }

    protected function importLines($datas, $type_compte = null) {
        $compte = $this->getCompte();
        $compte->etablissement = null;

        $id = null;
        $etablissement = null;
        foreach($datas as $data) {
            $id = $data[self::CSV_ID];
            try{
                $object = $this->importLine($data, $compte, $etablissement, $type_compte);
                if($object instanceof Etablissement) {
                    $etablissement = $object;
                }
            } catch (Exception $e) {
                echo sprintf("ERROR;%s;#LINE;%s\n", $e->getMessage(), implode(";", $data));
                return;
            }
        }

        if(!$type_compte) {
            $types_compte = $this->getTypesCompte($compte);

            if (count($types_compte) == 1) {
                foreach($types_compte as $tc => $null) {
                    $type_compte = $tc;                        
                }
            } else {
                foreach($types_compte as $tc => $null) {
                    $this->importLines($datas, $tc);
                    $this->echoWarning("Compte demultiplié", array($id, $tc));
                }
            }
        }

        if(!$type_compte) {
            return;
        }

        $compte->type_compte = $type_compte;

        if($compte->type_compte == CompteClient::TYPE_COMPTE_ETABLISSEMENT && !$compte->raison_sociale) {
            $compte->raison_sociale = trim(sprintf("%s %s %s", $compte->civilite, $compte->nom,  $compte->prenom));
            $compte->prenom = null;
            $compte->nom = null;
            $compte->civilite = null;
        }

        $compte->infos->attributs->remove('NON_CONDITIONNEUR');

        $compte->identifiant = $this->getIdentifiantCompte($compte, $id);

        if($etablissement && $etablissement->exist('date_connexion') && $etablissement->date_connexion) {
            $compte->adresse = $etablissement->adresse;
            $compte->commune = $etablissement->commune;
            $compte->code_postal = $etablissement->code_postal;

            $compte->email = $etablissement->email;
            $compte->telephone = $compte->telephone;
            $compte->telephone_prive = $etablissement->telephone_prive;
            $compte->telephone_mobile = $etablissement->telephone_mobile;
            $compte->fax = $etablissement->fax;
        }

        if($etablissement) {
            $etablissement->remove('code_insee');
            $etablissement->remove('nom');
            foreach($etablissement->familles as $famille_key => $null) {
                $compte->infos->attributs->add($famille_key, CompteClient::getInstance()->getAttributLibelle($famille_key));
            }

            if(!$etablissement->familles->exist(EtablissementClient::FAMILLE_CONDITIONNEUR)) {
                $compte->infos->attributs->remove(EtablissementClient::FAMILLE_CONDITIONNEUR);
            }

            $compte->chais = $etablissement->chais->toArray(true, false);
        }

        $this->save($compte, $etablissement);
    }

    protected function getCompte() {
        $compte = new Compte();

        return $compte;
    }

    protected function getIdentifiantCompte($compte, $id) {
        if($compte->type_compte == CompteClient::TYPE_COMPTE_ETABLISSEMENT) {

            return 'E'.$compte->cvi;
        } 

        $prefix = CompteClient::getInstance()->getPrefix($compte->type_compte);
        $identifiant = $prefix.$id;

        return $identifiant;
    }

    protected function getTypesCompte($compte) {
        $types_compte = array();

        foreach($compte->infos->attributs as $key => $libelle) {
            foreach(CompteClient::getInstance()->getAllTypesCompte() as $tc) {
                if(array_key_exists($key, CompteClient::getInstance()->getAttributsForType($tc))) {
                    $types_compte[$tc] = null;
                }
            }
        }

        if($compte->infos->attributs->exist('SYNDICAT')) {
            $compte->infos->attributs->remove('SYNDICAT');
            $types_compte['SYNDICAT'] = null;
        }

        if($compte->etablissement) {
            $types_compte[CompteClient::TYPE_COMPTE_ETABLISSEMENT] = null;
        }

        if (count($types_compte) == 0) {
            $types_compte[CompteClient::TYPE_COMPTE_CONTACT] = null;
        }

        return $types_compte;
    }

    protected function importLine($data, $compte, $etablissement, $type_compte) {

        if($data[self::CSV_TYPE_LIGNE] == "1.COMPTE") {
            
            return $this->importLineCompte($data, $compte);
        }

        if($data[self::CSV_TYPE_LIGNE] == "2.   CVI") {
            
            return $this->importLineCVI($data, $compte, $type_compte);
        }

        if($data[self::CSV_TYPE_LIGNE] == "3.  CHAI") {
            
            return $this->importLineChai($data, $compte, $etablissement, $type_compte);
        }

        if($data[self::CSV_TYPE_LIGNE] == "4.ATTRIB" && !$data[self::CSV_FAMILLE]) {

            return $this->importLineAttribut($data, $compte, $type_compte);
        }

        if($data[self::CSV_TYPE_LIGNE] == "4.ATTRIB" && $data[self::CSV_FAMILLE]) {

            return $this->importLineAttributAutre($data, $compte, $type_compte);
        }

        if($data[self::CSV_TYPE_LIGNE] == "5.COMMUN") {
            
            return $this->importLineCommunication($data, $compte);
        }

        if($data[self::CSV_TYPE_LIGNE] == "6.LIAISON") {

            return $this->importLineLiaison($data, $compte);
        }
    }

    protected function importLineCompte($data, $compte) {
        if(!preg_match("/^[0-9]{6}$/", $data[self::CSV_ID])) {

            throw new Exception("L'identifiant n'est pas au bon format");
        }

        if(preg_match("/4 - Possède une DR/", $data[self::CSV_RAISON_SOCIALE])) {
            throw new Exception("IGNORE : 4 - Possède une DR");
        }

        if(preg_match("/4 - Possède une DR/", $data[self::CSV_NOM])) {
            throw new Exception("IGNORE : 4 - Possède une DR");
        }

        if($data[self::CSV_RAISON_SOCIALE]) {
            $compte->raison_sociale = trim(sprintf("%s %s", $data[self::CSV_CIVILITE], $data[self::CSV_RAISON_SOCIALE]));
        } elseif($data[self::CSV_NOM]) {
            $compte->civilite = trim($data[self::CSV_CIVILITE]);
            $compte->nom = trim($data[self::CSV_NOM]);
            $compte->prenom = trim($data[self::CSV_PRENOM]);
        } else {
            throw new sfException("Aucun nom ou raison sociale");
        }

        $compte->siret = trim(str_replace(" ", "", $data[self::CSV_SIRET]));
        if($compte->siret && !preg_match("/^[0-9]+$/", $compte->siret)) {
            $compte->siret = null;
            $this->echoWarning(sprintf("Le SIRET n'est pas au bon format : %s", $compte->siret), $data);
        }

        $compte->no_accises = trim(str_replace(" ", "", $data[self::CSV_TVA_INTRA]));
        if($compte->no_accises && !preg_match("/^FR[0-9]+$/", $compte->no_accises)) {
            $compte->no_accises = null;
            $this->echoWarning(sprintf("Le numéro d'accises n'est pas au bon format : %s", $compte->no_accises), $data); 
        }

        $compte->adresse = $this->formatAdresse($data);
        if(!$compte->adresse) {
           $this->echoWarning("Adresse vide", $data); 
        }

        $compte->code_postal = trim($data[self::CSV_CODE_POSTAL]);
        
        if($compte->code_postal && !preg_match("/^[0-9]+$/", $compte->code_postal)) {
            $this->echoWarning("Code postal au mauvais format", $data);
        }

        $compte->commune = trim($data[self::CSV_COMMUNE]);

        $compte->pays = trim($data[self::CSV_PAYS]);

        if(!$compte->pays) {
            $compte->pays = "FRANCE";
        }

        if($compte->pays != "FRANCE") {
            $compte->commune = null;
            $compte->code_postal = null;
        }

        $compte->date_creation = $this->formatDate($data[self::CSV_DATE_CREATION]);

        $compte->date_archivage = null;
        if(trim($data[self::CSV_DATE_ARCHIVAGE])) {
            $compte->date_archivage = $this->formatDate($data[self::CSV_DATE_ARCHIVAGE]);
        }
        $compte->statut = 'ACTIF';

        if($compte->date_archivage) {
            $compte->statut = 'INACTIF';
        }

        if($compte->pays == "FRANCE" && !$compte->commune && !$compte->code_postal && !preg_match("/SYND/", $compte->raison_sociale)) {
            throw new Exception("IGNORE : Contact FRANCE sans code postal et commune");
        }

        if($compte->pays == "FRANCE") {
            if(!$compte->code_postal || $compte->code_postal == "Canton non précisé") {
                $this->echoWarning("Code postal non trouvé", $data);
            }
        }

        if($compte->pays == "FRANCE") {
            if(!$compte->commune || $compte->commune == "Canton non précisé") {
                $this->echoWarning("Ville non trouvé", $data);
            }
        }

        $compte->updateNomAAfficher();
    }

    protected function importLineCVI($data, $compte, $type_compte = null) {

        if($data[self::CSV_RAISON_SOCIALE] == "___VIRTUAL_EVV___") {
            
            return null;
        }

        if($type_compte && $type_compte != CompteClient::TYPE_COMPTE_ETABLISSEMENT) {

            return null;
        }

        if($compte->etablissement) {
           $this->echoWarning("Doublon de ligne CVI (Ignoré)", $data);

           return null;
        }

        $cvi = str_replace(" ", "", $data[self::CSV_CVI]);

        if(!preg_match("/^[0-9]{10}$/", $cvi)) {
            if($compte->date_archivage) {
              throw new Exception("Le CVI n'est pas au bon format mais archivé");  
            }
            throw new Exception("Le CVI n'est pas au bon format");
        }

        $compte->etablissement = 'ETABLISSEMENT-'.$cvi;
        $compte->cvi = $cvi;

        $etablissement = EtablissementClient::getInstance()->createOrFind($data[self::CSV_CVI]);
        $etablissement->chais = array();
        $etablissement->raison_sociale = $compte->nom_a_afficher;
        $etablissement->siret = $compte->siret;
        $etablissement->adresse = $compte->adresse;
        $etablissement->code_postal = $compte->code_postal;
        $etablissement->commune = $compte->commune;
        
        return $etablissement;
    }

    protected function echoWarning($message, $data) {

        echo sprintf("WARNING;%s;#LINE;%s\n", $message, implode(";", $data));
    }

    protected function importLineChai($data, $compte, $etablissement, $type_compte = null) {
        if($type_compte && $type_compte != CompteClient::TYPE_COMPTE_ETABLISSEMENT) {

            return null;
        }

        if(!$etablissement) {
            throw new sfException(sprintf("Chais sans etablissement: %s", $compte->etablissement));
        }

        $chai = $etablissement->chais->add();
        if($data[self::CSV_RAISON_SOCIALE] && !$data[self::CSV_ADRESSE_1]) {
            $data[self::CSV_ADRESSE_1] = $data[self::CSV_RAISON_SOCIALE];
        }

        $adresse = $this->formatAdresse($data);
        
        if(!$adresse) {
            $this->echoWarning("Chai sans adresse", $data);
            return;
        }
        $chai->adresse = $adresse;
        $chai->commune = $data[self::CSV_COMMUNE];
        $chai->code_postal = $data[self::CSV_CODE_POSTAL];

        if(!$chai->commune) {
            $this->echoWarning("Chai sans commune", $data);
        }

        if(!$chai->code_postal) {
            $this->echoWarning("Chai sans code postal", $data);
        }
    }

    protected function importLineCommunication($data, $compte) {
        $telephone = $this->formatPhone($data[self::CSV_TEL]);
        $mobile = $this->formatPhone($data[self::CSV_PORTABLE]);
        $fax = $this->formatPhone($data[self::CSV_FAX]);

        preg_match("/^([0-9]+)/", $data[self::CSV_FAMILLE], $matches);
        $nb_ordre = $matches[1];

        if($compte->telephone_bureau && $telephone && $telephone == $compte->telephone_bureau) {

        } elseif(!$compte->telephone_bureau && $telephone) {
            $compte->telephone_bureau = $telephone;
        } elseif($compte->telephone_prive && $telephone && $telephone == $compte->prive) {

        } elseif(!$compte->telephone_prive && $telephone) {
            $compte->telephone_prive = $telephone;
            echo sprintf("INFO;%s;#LINE;%s;#DOUBLON;%s;%s\n", "Telephone bureau en double => enregistré dans téléphone privé", implode(";", $data), $compte->telephone_bureau, $telephone); 
        } elseif($telephone) {
            echo sprintf("WARNING;%s;#LINE;%s;#DOUBLONS;%s;%s\n", "Telephone privé en double", implode(";", $data), $compte->telephone_prive, $telephone); 
        }

        if($compte->telephone_mobile && $mobile && $compte->telephone_mobile == $mobile) {

        } elseif(!$compte->telephone_mobile && $mobile) {
            $compte->telephone_mobile = $mobile;
        } elseif($compte->telephone_prive && $mobile && $compte->telephone_prive == $mobile) {

        } elseif($mobile && !$compte->telephone_prive) {
            $compte->telephone_prive = $mobile;
            echo sprintf("INFO;%s;#LINE;%s;#DOUBLONS;%s;%s\n", "Téléphone mobile en double => enregistré dans téléphone privé", implode(";", $data), $compte->telephone_prive, $mobile);
        } elseif($mobile) {
            echo sprintf("WARNING;%s;#LINE;%s;#DOUBLONS;%s;%s\n", "Téléphone mobile en double", implode(";", $data), $compte->telephone_mobile, $mobile);
        }

        if($compte->fax && $fax && $compte->fax == $fax) {

        } elseif(!$compte->fax && $fax) {
            $compte->fax = $fax;
        } elseif($fax) {
            echo sprintf("WARNING;%s;#LINE;%s;#DOUBLONS;%s;%s\n", "Fax en double", implode(";", $data), $compte->fax, $fax);
        }

        $email = trim($data[self::CSV_EMAIL]);
        if($email && !preg_match("/^[a-zA-Z0-9\._-]+@[a-zA-Z0-9\._-]+$/", $email)) {
            throw new Exception("L'email n'est pas au bon format"); 
        }

        $email = ($email) ? $email : null;

        $web = trim($data[self::CSV_WEB]);

        if($compte->email && $email && $compte->email == $email) {

        } elseif(!$compte->email && $email) {
            $compte->email = $email;
        } elseif($email) {
           echo sprintf("WARNING;%s;#LINE;%s;#DOUBLONS;%s;%s\n", "Email en double", implode(";", $data), $compte->email, $email); 
        }

        $compte->web = $web;
    }

    protected function importLineAttribut($data, $compte, $type_compte = null) {
        
        if(!$type_compte || $type_compte == CompteClient::TYPE_COMPTE_DEGUSTATEUR) {
            if(preg_match("/Porteurs de mémoires/", $data[self::CSV_ATTRIBUTS])) {
                $compte->infos->attributs->add(CompteClient::ATTRIBUT_DEGUSTATEUR_PORTEUR_MEMOIRES, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_DEGUSTATEUR_PORTEUR_MEMOIRES));
            }

            if(preg_match("/Techniciens du produit/", $data[self::CSV_ATTRIBUTS])) {
                $compte->infos->attributs->add(CompteClient::ATTRIBUT_DEGUSTATEUR_TECHNICIEN_PRODUIT, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_DEGUSTATEUR_TECHNICIEN_PRODUIT));
            }
        
            if(preg_match("/Usagers du produit/", $data[self::CSV_ATTRIBUTS])) {
                $compte->infos->attributs->add(CompteClient::ATTRIBUT_DEGUSTATEUR_USAGER_PRODUIT, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_DEGUSTATEUR_USAGER_PRODUIT));
            }
        }

        if(!$type_compte || $type_compte == CompteClient::TYPE_COMPTE_AGENT_PRELEVEMENT) {
            if(preg_match("/Préleveur/", $data[self::CSV_ATTRIBUTS])) {
                $compte->infos->attributs->add(CompteClient::ATTRIBUT_AGENT_PRELEVEMENT_PRELEVEUR, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_AGENT_PRELEVEMENT_PRELEVEUR));
            }

            if(preg_match("/Agent de contrôle/", $data[self::CSV_ATTRIBUTS])) {
                $compte->infos->attributs->add(CompteClient::ATTRIBUT_AGENT_PRELEVEMENT_AGENT_CONTROLE, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_AGENT_PRELEVEMENT_AGENT_CONTROLE));
            }
        }

        if(!$type_compte || $type_compte != CompteClient::TYPE_COMPTE_ETABLISSEMENT) {
            if(preg_match("/Vinificateur/", $data[self::CSV_ATTRIBUTS])) {
                $compte->infos->attributs->add(CompteClient::ATTRIBUT_ETABLISSEMENT_VINIFICATEUR, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_ETABLISSEMENT_VINIFICATEUR));
            }

            if(preg_match("/^Elaborateur$/", $data[self::CSV_ATTRIBUTS])) {
                $compte->infos->attributs->add(CompteClient::ATTRIBUT_ETABLISSEMENT_ELABORATEUR, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_ETABLISSEMENT_ELABORATEUR));
            }

            if(preg_match("/Producteur de raisins en structure collective/", $data[self::CSV_ATTRIBUTS])) {
                $compte->infos->attributs->add(CompteClient::ATTRIBUT_ETABLISSEMENT_COOPERATEUR, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_ETABLISSEMENT_COOPERATEUR));
            }

            if(preg_match("/Producteur/", $data[self::CSV_ATTRIBUTS])) {
                $compte->infos->attributs->add(CompteClient::ATTRIBUT_ETABLISSEMENT_PRODUCTEUR_RAISINS, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_ETABLISSEMENT_PRODUCTEUR_RAISINS));
            }

            if(preg_match("/Distillation/", $data[self::CSV_ATTRIBUTS])) {
                $compte->infos->attributs->add(CompteClient::ATTRIBUT_ETABLISSEMENT_DISTILLATEUR, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_ETABLISSEMENT_DISTILLATEUR));
            }

            /*if(preg_match("/laborateur/", $data[self::CSV_ATTRIBUTS])) {
                $compte->infos->attributs->add(CompteClient::ATTRIBUT_ETABLISSEMENT_ELABORATEUR, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_ETABLISSEMENT_ELABORATEUR));
            }*/

            if(preg_match("/Négoce/", $data[self::CSV_ATTRIBUTS])) {
                $compte->infos->attributs->add(CompteClient::ATTRIBUT_ETABLISSEMENT_NEGOCIANT, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_ETABLISSEMENT_NEGOCIANT));
            }

            if(preg_match("/Cave coopérative/", $data[self::CSV_ATTRIBUTS])) {
                $compte->infos->attributs->add(CompteClient::ATTRIBUT_ETABLISSEMENT_CAVE_COOPERATIVE, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_ETABLISSEMENT_CAVE_COOPERATIVE));
            }

            if(preg_match("/Metteur en marché/", $data[self::CSV_ATTRIBUTS])) {
                $compte->infos->attributs->add(CompteClient::ATTRIBUT_ETABLISSEMENT_METTEUR_EN_MARCHE, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_ETABLISSEMENT_METTEUR_EN_MARCHE));
            }

            if(preg_match("/Conditionneur/", $data[self::CSV_ATTRIBUTS])) {
                $compte->infos->attributs->add(CompteClient::ATTRIBUT_ETABLISSEMENT_CONDITIONNEUR, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_ETABLISSEMENT_CONDITIONNEUR));
            }

            if(preg_match("/Viticulteur indépendant/", $data[self::CSV_ATTRIBUTS])) {
                $compte->infos->attributs->add(CompteClient::ATTRIBUT_ETABLISSEMENT_VITICULTEUR_INDEPENDANT, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_ETABLISSEMENT_VITICULTEUR_INDEPENDANT));
            }
        }

        if(!$type_compte && $data[self::CSV_ATTRIBUTS] == "SYNDICAT") {
           $compte->infos->attributs->add("SYNDICAT", "SYNDICAT"); 
        }  

        // Pour test
        if($data[self::CSV_ATTRIBUTS] == "Prestataire de service") {
            $libelle = "Prestataire de service";
            $compte->infos->manuels->add($this->getAttributManuelKey($libelle), $libelle);
        }
    }

    protected function importLineAttributAutre($data, $compte) {
        if(!$data[self::CSV_ATTRIBUTS]) {

            return;
        }

        if($data[self::CSV_ATTRIBUTS] == "Abonnés Revue \"Les Vins d'Alsace\"") {
            $libelle = "Abonné revue";
            $compte->infos->manuels->add($this->getAttributManuelKey($libelle), $libelle);
        }

        if($data[self::CSV_ATTRIBUTS] == "Clients Capsules") {
            $libelle = "Clients Capsules";
            $compte->infos->manuels->add($this->getAttributManuelKey($libelle), $libelle);
        }

        if($data[self::CSV_ATTRIBUTS] == "Œnologue") {
            $libelle = "Oenologue";
            $compte->infos->manuels->add($this->getAttributManuelKey($libelle), $libelle);
        }

        if($data[self::CSV_ATTRIBUTS] == "Conseil d'administration") {
            $libelle = "Conseil d'administration";
            $compte->infos->manuels->add($this->getAttributManuelKey($libelle), $libelle);
        }

        if($data[self::CSV_ATTRIBUTS] == "Présidents des syndicats viticoles 68") {
            $libelle = "Présidents des syndicats viticoles 68";
            $compte->infos->manuels->add($this->getAttributManuelKey($libelle), $libelle);
        }

        if($data[self::CSV_ATTRIBUTS] == "Courtier") {
            $libelle = "Courtier";
            $compte->infos->manuels->add($this->getAttributManuelKey($libelle), $libelle);
        }

        if($data[self::CSV_ATTRIBUTS] == "Personnel AVA et Centre de dégustation") {
            $libelle = "Personnel AVA et Centre de dégustation";
            $compte->infos->manuels->add($this->getAttributManuelKey($libelle), $libelle);
        }

        if($data[self::CSV_ATTRIBUTS] == "Bureau") {
            $libelle = "Membre du bureau";
            $compte->infos->manuels->add($this->getAttributManuelKey($libelle), $libelle);
        }

        if($data[self::CSV_ATTRIBUTS] == "Sommelier") {
            $libelle = "Sommelier";
            $compte->infos->manuels->add($this->getAttributManuelKey($libelle), $libelle);
        }

        if($data[self::CSV_ATTRIBUTS] == "Laboratoires") {
            $libelle = "Laboratoires";
            $compte->infos->manuels->add($this->getAttributManuelKey($libelle), $libelle);
        }

        /*if($data[self::CSV_ATTRIBUTS] == "Vice-Président") {
            $libelle = "Vice-Président";
            $compte->infos->manuels->add($this->getAttributManuelKey($libelle), $libelle);
        }

        if($data[self::CSV_ATTRIBUTS] == "Trésorier Adjoint") {
            $libelle = "Trésorier Adjoint";
            $compte->infos->manuels->add($this->getAttributManuelKey($libelle), $libelle);
        }

        if($data[self::CSV_ATTRIBUTS] == "Secrétaire Général") {
            $libelle = "Secrétaire Général";
            $compte->infos->manuels->add($this->getAttributManuelKey($libelle), $libelle);
        }

        if($data[self::CSV_ATTRIBUTS] == "Trésorier") {
            $libelle = "Trésorier";
            $compte->infos->manuels->add($this->getAttributManuelKey($libelle), $libelle);
        }

        if($data[self::CSV_ATTRIBUTS] == "Trésorier") {
            $libelle = "Trésorier";
            $compte->infos->manuels->add($this->getAttributManuelKey($libelle), $libelle);
        }*/

    }

    protected function getAttributManuelKey($libelle) {
        return str_replace('-', '_',strtoupper(KeyInflector::slugify($libelle)));
    }

    protected function importLineLiaison($data, $compte) {
        if(!trim($data[self::CSV_LIAISON])) {
            $this->echoWarning('Liaison inexistante', $data);
        }
        if(!trim($data[self::CSV_LIAISON_NOM])) {
            $this->echoWarning('Liaison nom inexistante', $data);
        }
        $compte->infos->syndicats->add("COMPTE-S".trim($data[self::CSV_LIAISON]), trim($data[self::CSV_LIAISON_NOM]));
    }

    protected function formatAdresse($data) {

        return trim(preg_replace("/[ ]+/", " ", sprintf("%s %s %s", $data[self::CSV_ADRESSE_1], $data[self::CSV_ADRESSE_2], $data[self::CSV_ADRESSE_3], $data[self::CSV_CEDEX])));
    }

    protected function formatPhone($numero) {
        $numero = trim(preg_replace("/[ xœ_\.]+/", "", $numero));
        if($numero && !preg_match("/^[0-9]{7,15}$/", $numero)) {
            throw new Exception(sprintf("Téléphone invalide : %s", $numero)); 
        }

        return ($numero) ? $numero : null;
    }

    protected function formatDate($date) {
        if(!$date) {
            return null;
        }

        $dateObj = new DateTime($date);

        return $dateObj->format('Y-m-d');
    }

}
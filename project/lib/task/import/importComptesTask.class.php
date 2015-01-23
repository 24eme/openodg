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

    protected $types_ignore = array();

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('file', sfCommandArgument::REQUIRED, "Fichier csv pour l'import"),
            new sfCommandArgument('types_ignore', sfCommandArgument::IS_ARRAY, "Types de compte à ignorés"),
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
        $this->types_ignore = $arguments['types_ignore'];
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

        if(in_array($type_compte, $this->types_ignore)) {
            $this->echoWarning(sprintf("Compte %s ignoré", $type_compte), array($id));
            return;
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

        if(
            $compte->adresse_complement_destinataire && 
            !$compte->raison_sociale && 
            !preg_match("/(Service|Vallée|Centre|Domaine|Z\.A|Zone Artisanale|BUREAU|PRESIDENT|Chez|B\.P\.|Lieu-dit|Résidence|CONSEILLER|Florimont|PREFET|Z\.I\.|Zone Industrielle|ZA|Sénateur|maire|parc|Immeuble|restaurant|Vignoble|Hôtel|Hotel|SENATEUR|Exploitation Viticole|Expert comptable|Expert-Comptable|DEPUTE|députe|Député|Weingut Rappenhof|viticole|Localita|Traitement de l'Information|RESERVATIONS|RENSEIGNEMENT|MME|Château|Comité|Avocat|cité|Bât|BAT\.|BADISCHE|ESPACE)/i", $compte->adresse_complement_destinataire) &&
            !preg_match("/^(AU|LE|LA|LES|M\.|CLOS|Ferme|MAIS\.|Madame|MONSIEUR|Mme|COMMUNE|ville|Documentation|ESPACE|CS|ABS|Indemnisat|Information|BP|CITE|maison de|maison des|Cellier) /i", $compte->adresse_complement_destinataire) && 
            !preg_match("/^(Coopérative Vinicole|Cave Coopérative Vinicole|Cooéprative Viinicole|Cave Vinicole|COOPERATIVE VINICOLE|DISTILERIE ARTISANALE)$/i", $compte->adresse_complement_destinataire)
           ) {
            $compte->raison_sociale = $compte->adresse_complement_destinataire;
            $compte->adresse_complement_destinataire = null;
        }

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

        if($compte->infos->attributs->exist(CompteClient::TYPE_COMPTE_DEGUSTATEUR)) {
            if(!array_key_exists(CompteClient::TYPE_COMPTE_DEGUSTATEUR, $types_compte)) {
                $this->echoWarning("Degustateur forcé", array($compte->identifiant_interne));
            }
            $compte->infos->attributs->remove(CompteClient::TYPE_COMPTE_DEGUSTATEUR);
            $types_compte[CompteClient::TYPE_COMPTE_DEGUSTATEUR] = null;
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

        if($data[self::CSV_TYPE_LIGNE] == "7.COMMEN") {

            return $this->importLineCommentaires($data, $compte);
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

        $compte->identifiant_interne = $data[self::CSV_ID];

        if(trim($data[self::CSV_RAISON_SOCIALE])) {
            $compte->raison_sociale = trim(preg_replace("/[ ]+/", " ", sprintf("%s %s", $data[self::CSV_CIVILITE], $data[self::CSV_RAISON_SOCIALE])));
        } elseif(trim($data[self::CSV_NOM])) {
            $compte->civilite = trim(preg_replace("/[ ]+/", " ", $data[self::CSV_CIVILITE]));
            $compte->nom = trim(preg_replace("/[ ]+/", " ", $data[self::CSV_NOM]));
            $compte->prenom = trim(preg_replace("/[ ]+/", " ", $data[self::CSV_PRENOM]));
        } else {
            throw new sfException("Aucun nom ou raison sociale");
        }

        if($compte->nom && !$compte->raison_sociale && !$compte->prenom && !$compte->civilite && !preg_match("/^L[EA]{1} /i", $compte->nom)) {
            $compte->raison_sociale = $compte->nom;
            $compte->nom = null; 
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

        $adresses = $this->formatAdresse($data);
        $compte->adresse = $adresses['adresse'];
        $compte->adresse_complement_destinataire = $adresses['precision'];
        $compte->adresse_complement_lieu = $adresses['complement'];

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

        if($compte->date_creation && $compte->date_archivage && $compte->date_archivage < $compte->date_creation) {
            $compte->date_archivage = $compte->date_creation;
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
        if($etablissement->familles->exist(CompteClient::ATTRIBUT_ETABLISSEMENT_ELABORATEUR)) {
            $etablissement->familles->remove(CompteClient::ATTRIBUT_ETABLISSEMENT_ELABORATEUR);
        }

        if($etablissement->familles->exist('COOPERATEUR')) {
            $etablissement->familles->remove('COOPERATEUR');
        }
        
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

        $adresse = $this->formatAdresseSimple($data);
        
        if(!$adresse) {
            $this->echoWarning("Chai sans adresse", $data);
            return;
        }
        $chai->adresse = $adresse;
        $chai->commune = $data[self::CSV_COMMUNE];
        $chai->code_postal = $data[self::CSV_CODE_POSTAL];

        $attributs = $data[self::CSV_ATTRIBUTS];

        if(preg_match("/CHAI_DE_VINIFICATION/", $attributs)) {
            $chai->attributs->add(CompteClient::CHAI_ATTRIBUT_VINIFICATION, CompteClient::getInstance()->getChaiAttributLibelle(CompteClient::CHAI_ATTRIBUT_VINIFICATION));
        }
        if(preg_match("/CENTRE_DE_CONDITIONNEMENT/", $attributs)) {
            $chai->attributs->add(CompteClient::CHAI_ATTRIBUT_CONDITIONNEMENT, CompteClient::getInstance()->getChaiAttributLibelle(CompteClient::CHAI_ATTRIBUT_CONDITIONNEMENT));
        }
        if(preg_match("/LIEU_DE_STOCKAGE/", $attributs)) {
            $chai->attributs->add(CompteClient::CHAI_ATTRIBUT_STOCKAGE, CompteClient::getInstance()->getChaiAttributLibelle(CompteClient::CHAI_ATTRIBUT_STOCKAGE));
        }
        if(preg_match("/CENTRE_DE_PRESSURAGE/", $attributs)) {
            $chai->attributs->add(CompteClient::CHAI_ATTRIBUT_PRESSURAGE, CompteClient::getInstance()->getChaiAttributLibelle(CompteClient::CHAI_ATTRIBUT_PRESSURAGE));
        }

        if(!$chai->commune) {
            $this->echoWarning("Chai sans commune", $data);
        }

        if(!$chai->code_postal) {
            $this->echoWarning("Chai sans code postal", $data);
        }

        if(!count($chai->attributs->toArray(true, false))) {
            $this->echoWarning("Chai sans attribut", $data);
        }
    }

    protected function importLineCommunication($data, $compte) {
        
        if($data[self::CSV_FAMILLE] == 'multi' && strlen(preg_replace("/[ \.]+/", "", $data[self::CSV_TEL])) == 20) {
            $telephone_tmp = preg_replace("/[ \.]+/", "", $data[self::CSV_TEL]);
            $data[self::CSV_TEL] = substr($telephone_tmp, 0, 10);
            $data[self::CSV_PORTABLE] = substr($telephone_tmp, 10, 10);
            $this->echoWarning("Téléhpone dédoublé", array($data[self::CSV_TEL]));
            $this->echoWarning("Mobile dédoublé", array($data[self::CSV_PORTABLE]));
        }

        if($data[self::CSV_FAMILLE] == 'multi' && preg_match('/fax /i', $data[self::CSV_EMAIL])) {
            $data[self::CSV_FAX] = preg_replace('/[a-zA-Z: ]+/', "", $data[self::CSV_EMAIL]);
            $data[self::CSV_EMAIL] = null;
        }

        $telephone = $this->formatPhone($data[self::CSV_TEL]);
        $mobile = $this->formatPhone($data[self::CSV_PORTABLE]);
        $fax = $this->formatPhone($data[self::CSV_FAX]);

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

        $email = str_replace("œ", "oe", str_replace(",", ".", str_replace(" ", "", $data[self::CSV_EMAIL])));
        if($email && !preg_match("/^[a-zA-Z0-9\._-]+@[a-zA-Z0-9\._-]+$/", $email)) {
            throw new Exception("L'email n'est pas au bon format"); 
        }

        $email = ($email) ? $email : null;

        if($compte->email && $email && $compte->email == $email) {

        } elseif(!$compte->email && $email) {
            $compte->email = $email;
        } elseif($email) {
           echo sprintf("WARNING;%s;#LINE;%s;#DOUBLONS;%s;%s\n", "Email en double", implode(";", $data), $compte->email, $email); 
        }
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
                $compte->infos->attributs->add(CompteClient::ATTRIBUT_AGENT_PRELEVEMENT_AGENT_PRELEVEMENT, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_AGENT_PRELEVEMENT_AGENT_PRELEVEMENT));
            }

            if(preg_match("/Agent de contrôle/", $data[self::CSV_ATTRIBUTS])) {
                $compte->infos->attributs->add(CompteClient::ATTRIBUT_AGENT_PRELEVEMENT_APPUI_TECHNIQUE, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_AGENT_PRELEVEMENT_APPUI_TECHNIQUE));
            }
        }

        if(!$type_compte || $type_compte == CompteClient::TYPE_COMPTE_ETABLISSEMENT) {
            if(preg_match("/Vinificateur/", $data[self::CSV_ATTRIBUTS])) {
                $compte->infos->attributs->add(CompteClient::ATTRIBUT_ETABLISSEMENT_VINIFICATEUR, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_ETABLISSEMENT_VINIFICATEUR));
            }

            if(preg_match("/^Elaborateur$/", $data[self::CSV_ATTRIBUTS])) {
                $compte->infos->attributs->add(CompteClient::ATTRIBUT_ETABLISSEMENT_ELABORATEUR, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_ETABLISSEMENT_ELABORATEUR));
            }

            if(preg_match("/Producteur de raisins en structure collective/", $data[self::CSV_ATTRIBUTS])) {
                $compte->infos->attributs->add(CompteClient::ATTRIBUT_ETABLISSEMENT_APPORTEUR, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_ETABLISSEMENT_APPORTEUR));
            }

            if(preg_match("/Producteur/", $data[self::CSV_ATTRIBUTS])) {
                $compte->infos->attributs->add(CompteClient::ATTRIBUT_ETABLISSEMENT_PRODUCTEUR_RAISINS, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_ETABLISSEMENT_PRODUCTEUR_RAISINS));
            }

            if(preg_match("/donneur d'ordre/", $data[self::CSV_ATTRIBUTS])) {
                $compte->infos->attributs->add(CompteClient::ATTRIBUT_ETABLISSEMENT_DONNEUR_ORDRE, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_ETABLISSEMENT_DONNEUR_ORDRE));
            }

            if(preg_match("/Distillation/", $data[self::CSV_ATTRIBUTS])) {
                $compte->infos->attributs->add(CompteClient::ATTRIBUT_ETABLISSEMENT_DISTILLATEUR, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_ETABLISSEMENT_DISTILLATEUR));
            }

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

        if(!$type_compte && $data[self::CSV_ATTRIBUTS] == "Degustateur") {
           $compte->infos->attributs->add(CompteClient::TYPE_COMPTE_DEGUSTATEUR, CompteClient::TYPE_COMPTE_DEGUSTATEUR); 
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

    protected function importLineCommentaires($data, $compte) {
        if($compte->commentaires) {
            $compte->commentaires .= "\n";
        }
        $compte->commentaires .= $data[self::CSV_ATTRIBUTS]; 
    }

    protected function formatAdresseSimple($data) {

        return trim(preg_replace("/[ ]+/", " ", sprintf("%s %s %s", $data[self::CSV_ADRESSE_1], $data[self::CSV_ADRESSE_2], $data[self::CSV_ADRESSE_3])));
    }

    protected function formatAdresse($data) {
        $adresse = array("adresse" => null, "precision" => null, "complement" => null);
        
        $voie_1 = $this->formatVoie($data[self::CSV_ADRESSE_1], $data);
        
        if($voie_1) {
            $adresse['adresse'] = $voie_1;
            $adresse['complement'] = $this->formatAdresseComplement($data[self::CSV_ADRESSE_2]." - ".$data[self::CSV_ADRESSE_3], $data);

            return $adresse;
        }

        $voie_2 = $this->formatVoie($data[self::CSV_ADRESSE_2], $data);

        if($voie_2) {
            $adresse['precision'] = $this->formatAdresseComplement($data[self::CSV_ADRESSE_1], $data);
            $adresse['adresse'] = $voie_2;
            $adresse['complement'] = $this->formatAdresseComplement($data[self::CSV_ADRESSE_3], $data);

            return $adresse;
        }

        $voie_3 = $this->formatVoie($data[self::CSV_ADRESSE_3], $data);

        if($voie_3) {
            $adresse['adresse'] = $voie_3;
            if($this->isComplement($data[self::CSV_ADRESSE_2])) {
                $adresse['precision'] = $this->formatAdresseComplement($data[self::CSV_ADRESSE_1], $data);
                $adresse['complement'] = $this->formatAdresseComplement($data[self::CSV_ADRESSE_2], $data);
            } else {
                $adresse['precision'] = $this->formatAdresseComplement($data[self::CSV_ADRESSE_1] . " - " . $data[self::CSV_ADRESSE_2], $data);
            }

            return $adresse;
        }

        return $adresse;
    }

    protected function isComplement($complement) {
        $complement = trim(preg_replace("/[ ]+/", " ", $complement));

        if(preg_match("/^B.P. [0-9]+$/", $complement)) {

            return true;
        }

        if(preg_match("/^BP [0-9]+$/", $complement)) {

            return true;
        }

        return false;
    }

    protected function formatAdresseComplement($complement, $data) {
        $complement = trim(preg_replace("/[ ]+/", " ", $complement));
        $complement = trim(preg_replace("/-$/", "", $complement));
        $complement = trim(preg_replace("/^-/", "", $complement));

        if(!$complement) {

            return null;
        }

        return $complement;
    }

    protected function formatVoie($adresse, $data) {
        $adresse = trim(preg_replace("/[ ]+/", " ", $adresse));

        $preg_voie = "(rue | rue|'rue|place |route |impasse |avenue |boulevard |quai |Haut Village|bas village|faubourg|rte |passage| rn |lotissement|square|pré |basse|sentier|voie|fbg | av |bas-village|haut-village|marché|chemin|r\.n\.|r\.d\.)";

        if(preg_match("/Cédex/", $adresse)) {

            return null;
        }

        if(preg_match("/6EME JOUR/", $adresse)) {

            return null;
        }

        if(preg_match("/^B\.P\. [0-9]+$/", $adresse)) {

            return null;
        }

        if(preg_match("/^RN [0-9]+$/", $adresse)) {

            return $adresse;
        }

        if(preg_match("/^POSTFACH [0-9]+$/", $adresse)) {

            return null;
        }

        if (preg_match("/^[0-9]{1,3}[0-9a-zA-Z-]*[ ]+/", trim($adresse))) {

            return $adresse;
        }

        if (preg_match("/$preg_voie/i", $adresse)) {

            return $adresse;
        }

        if(preg_match("/^Via /", $adresse)) {

            return $adresse;
        }

        if (trim($data[self::CSV_PAYS]) && $data[self::CSV_PAYS] != "FRANCE" && preg_match("/[ ]+[0-9a-zA-Z-]*[0-9]{1,3}$/", $adresse)) {
            
            return $adresse;
        }

        return null;
    }

    protected function formatPhone($numero) {
        $numero = trim(preg_replace('/[ xœ_\.]+/', "", $numero));
        if($numero && !preg_match("/^[0-9]{7,15}$/", $numero)) {
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
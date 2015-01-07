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
    const CSV_TEL                   = 18;
    const CSV_FAX                   = 19;
    const CSV_PORTABLE              = 20;
    const CSV_EMAIL                 = 21;
    const CSV_WEB                   = 22;
    const CSV_ATTRIBUTS             = 23;
    const CSV_DATE_ARCHIVAGE        = 24;
    const CSV_DATE_CREATION         = 25;

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

        $compte_existant = CompteClient::getInstance()->find($compte->_id);
        if($compte_existant) {
            $compte_existant->delete();
        }
        $compte->save();

        if($etablissement) {
            $etablissement->constructId();
            if($etablissement->isNew()) {
                echo sprintf("SUCCESS;%s;%s\n", "Création", $etablissement->_id);
            } else {
                echo sprintf("SUCCESS;%s;%s\n", "Mise à jour", $etablissement->_id);
            }
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
                    echo sprintf("WARNING;%s;#LINE;%s;%s\n", "Compte demultiplié", $id, $tc);
                }
            }
        }

        if(!$type_compte) {
            return;
        }

        $compte->type_compte = $type_compte;
        $compte->identifiant = $this->getIdentifiantCompte($compte, $id);

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

        foreach($compte->getAttributs() as $key => $libelle) {
            foreach(CompteClient::getInstance()->getAllTypesCompte() as $tc) {
                if(array_key_exists($key, CompteClient::getInstance()->getAttributsForType($tc))) {
                    $types_compte[$tc] = null;
                }
            }
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

        if($data[self::CSV_TYPE_LIGNE] == "5.COMMUN") {
            
            return $this->importLineCommunication($data, $compte);
        }

        if($data[self::CSV_TYPE_LIGNE] == "4.ATTRIB") {

            return $this->importLineAttribut($data, $compte, $type_compte);
        }
    }

    protected function importLineCompte($data, $compte) {
        if(!preg_match("/^[0-9]{6}$/", $data[self::CSV_ID])) {

            throw new Exception("'identifiant n'est pas au bon format");
        }

        if($data[self::CSV_RAISON_SOCIALE]) {
            $compte->raison_sociale = trim(sprintf("%s %s", $data[self::CSV_CIVILITE], $data[self::CSV_RAISON_SOCIALE]));
        } elseif($data[self::CSV_NOM]) {
            $compte->civilite = $data[self::CSV_CIVILITE];
            $compte->nom = $data[self::CSV_NOM];
            $compte->prenom = $data[self::CSV_PRENOM];
        } else {
            throw new sfException("Aucun nom ou raison sociale");
        }
        $siret = str_replace(" ", "", $data[self::CSV_SIRET]);
        if($siret && !preg_match("/^[0-9]+$/", $siret)) {
            throw new Exception(sprintf("Le SIRET n'est pas au bon format : %s", $siret));
        }
        $compte->siret = $siret;

        $no_accises = str_replace(" ", "", $data[self::CSV_TVA_INTRA]);
        if($no_accises && !preg_match("/^FR[0-9]+$/", $no_accises)) {
            throw new Exception(sprintf("Le numéro d'accises n'est pas au bon format : %s", $no_accises));
        }
        $compte->no_accises = $no_accises;

        $compte->adresse = $this->formatAdresse($data);
        $compte->code_postal = $data[self::CSV_CODE_POSTAL];
        $compte->commune = $data[self::CSV_COMMUNE];
        $compte->cedex = $data[self::CSV_CEDEX];

        $compte->date_creation = null;
        $compte->date_archivage = null;

        $compte->statut = 'ACTIF';

        if($data[self::CSV_DATE_ARCHIVAGE]) {
            $compte->statut = 'INACTIF';
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

        $cvi = str_replace(" ", "", $data[self::CSV_CVI]);

        if(!preg_match("/^[0-9]{10}$/", $cvi)) {

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
            throw new sfException("Adresse vide");
        }
        $chai->adresse = $adresse;
        $chai->commune = $data[self::CSV_COMMUNE];
        $chai->code_postal = $data[self::CSV_CODE_POSTAL];
    }

    protected function importLineCommunication($data, $compte) {

        $telephone = $this->formatPhone($data[self::CSV_TEL]);
        $mobile = $this->formatPhone($data[self::CSV_PORTABLE]);
        $fax = $this->formatPhone($data[self::CSV_FAX]);  

        if($data[self::CSV_FAMILLE] == "bur") {
            $compte->telephone_bureau = $telephone;
            $compte->telephone_mobile = $mobile;
            $compte->fax = $fax;  
        }

        if($data[self::CSV_FAMILLE] != "bur") {
            if($telephone) {
                $compte->telephone_prive = $telephone;
            }

            if($telephone && !$compte->telephone_bureau) {
                $compte->telephone_bureau = $telephone;
            }

            if($fax && !$compte->fax) {
                $compte->fax = $fax;
            }

            if($mobile) {
                $compte->telephone_prive = $mobile;
            }

            if($mobile && !$compte->telephone_mobile) {
                $compte->telephone_mobile = $mobile;
            }
        }

        $email = trim($data[self::CSV_EMAIL]);
        if($email && !preg_match("/^[a-zA-Z0-9\._-]+@[a-zA-Z0-9\._-]+$/", $email)) {
            throw new Exception("L'email n'est pas au bon format"); 
        }

        $email = ($email) ? $email : null;

        $web = trim($data[self::CSV_WEB]);

        if($data[self::CSV_FAMILLE] == "bur") {
            $compte->email = $email;  
            $compte->web = $web;  
        }

        if($email && !$compte->email && $data[self::CSV_FAMILLE] == "pri") {
            $compte->email = $email;  
        }

        if($web && !$compte->web && $data[self::CSV_FAMILLE] == "pri") {
            $compte->web = $web;  
        }
    }

    protected function importLineAttribut($data, $compte, $type_compte = null) {
        
        if(preg_match("/Porteurs de mémoires/", $data[self::CSV_ATTRIBUTS])) {
            $compte->tags->attributs->add(CompteClient::ATTRIBUT_DEGUSTATEUR_PORTEUR_MEMOIRES, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_DEGUSTATEUR_PORTEUR_MEMOIRES));
        }

        if(preg_match("/Techniciens du produit/", $data[self::CSV_ATTRIBUTS])) {
            $compte->tags->attributs->add(CompteClient::ATTRIBUT_DEGUSTATEUR_TECHNICIEN_PRODUIT, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_DEGUSTATEUR_TECHNICIEN_PRODUIT));
        }

        if(preg_match("/Usagers du produit/", $data[self::CSV_ATTRIBUTS])) {
            $compte->tags->attributs->add(CompteClient::ATTRIBUT_DEGUSTATEUR_USAGER_PRODUIT, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_DEGUSTATEUR_USAGER_PRODUIT));
        }

        if(preg_match("/Préleveur/", $data[self::CSV_ATTRIBUTS])) {
            $compte->tags->attributs->add(CompteClient::ATTRIBUT_AGENT_PRELEVEMENT_PRELEVEUR, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_AGENT_PRELEVEMENT_PRELEVEUR));
        }

        if(preg_match("/Agent de contrôle/", $data[self::CSV_ATTRIBUTS])) {
            $compte->tags->attributs->add(CompteClient::ATTRIBUT_AGENT_PRELEVEMENT_AGENT_CONTROLE, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_AGENT_PRELEVEMENT_AGENT_CONTROLE));
        }

        if($type_compte && $type_compte != CompteClient::TYPE_COMPTE_ETABLISSEMENT) {

            return null;
        }

        if(preg_match("/Vinificateur/", $data[self::CSV_ATTRIBUTS])) {
            $compte->tags->attributs->add(CompteClient::ATTRIBUT_ETABLISSEMENT_VINIFICATEUR, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_ETABLISSEMENT_VINIFICATEUR));
        }

        if(preg_match("/Producteur de raisins en structure collective/", $data[self::CSV_ATTRIBUTS])) {
            $compte->tags->attributs->add(CompteClient::ATTRIBUT_ETABLISSEMENT_COOPERATEUR, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_ETABLISSEMENT_COOPERATEUR));
        }

        if(preg_match("/Producteur/", $data[self::CSV_ATTRIBUTS])) {
            $compte->tags->attributs->add(CompteClient::ATTRIBUT_ETABLISSEMENT_PRODUCTEUR_RAISINS, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_ETABLISSEMENT_PRODUCTEUR_RAISINS));
        }

        if(preg_match("/Distillation/", $data[self::CSV_ATTRIBUTS])) {
            $compte->tags->attributs->add(CompteClient::ATTRIBUT_ETABLISSEMENT_DISTILLATEUR, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_ETABLISSEMENT_DISTILLATEUR));
        }

        if(preg_match("/laborateur/", $data[self::CSV_ATTRIBUTS])) {
            $compte->tags->attributs->add(CompteClient::ATTRIBUT_ETABLISSEMENT_ELABORATEUR, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_ETABLISSEMENT_ELABORATEUR));
        }

        if(preg_match("/Négoce/", $data[self::CSV_ATTRIBUTS])) {
            $compte->tags->attributs->add(CompteClient::ATTRIBUT_ETABLISSEMENT_NEGOCE, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_ETABLISSEMENT_NEGOCE));
        }

        if(preg_match("/Cave coopérative/", $data[self::CSV_ATTRIBUTS])) {
            $compte->tags->attributs->add(CompteClient::ATTRIBUT_ETABLISSEMENT_CAVE_COOPERATIVE, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_ETABLISSEMENT_CAVE_COOPERATIVE));
        }

        if(preg_match("/Metteur en marché/", $data[self::CSV_ATTRIBUTS])) {
            $compte->tags->attributs->add(CompteClient::ATTRIBUT_ETABLISSEMENT_METTEUR_EN_MARCHE, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_ETABLISSEMENT_METTEUR_EN_MARCHE));
        }

        if(preg_match("/Conditionneur/", $data[self::CSV_ATTRIBUTS])) {
            $compte->tags->attributs->add(CompteClient::ATTRIBUT_ETABLISSEMENT_CONDITIONNEUR, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_ETABLISSEMENT_CONDITIONNEUR));
        }

        if(preg_match("/Viticulteur indépendant/", $data[self::CSV_ATTRIBUTS])) {
            $compte->tags->attributs->add(CompteClient::ATTRIBUT_ETABLISSEMENT_VITICULTEUR_INDEPENDANT, CompteClient::getInstance()->getAttributLibelle(CompteClient::ATTRIBUT_ETABLISSEMENT_VITICULTEUR_INDEPENDANT));
        }        
    }

    protected function formatAdresse($data) {

        return trim(preg_replace("/[ ]+/", " ", sprintf("%s %s %s", $data[self::CSV_ADRESSE_1], $data[self::CSV_ADRESSE_2], $data[self::CSV_ADRESSE_3])));
    }

    protected function formatPhone($numero) {
        $numero = trim(preg_replace("/[ xœ_\.]+/", "", $numero));
        if($numero && !preg_match("/^[0-9]{8,11}$/", $numero)) {
            throw new Exception(sprintf("Téléphone invalide : %s", $numero)); 
        }

        return ($numero) ? $numero : null;
    }

}
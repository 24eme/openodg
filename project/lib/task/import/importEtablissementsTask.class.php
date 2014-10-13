<?php

class importEtablissementsTask extends sfBaseTask
{

    const CSV_CVI                   = 0;
    const CSV_TYPE_LIGNE            = 1;
    const CSV_RAISON_SOCIALE        = 2;
    const CSV_ADRESSE_1             = 3;
    const CSV_ADRESSE_2             = 4;
    const CSV_ADRESSE_3             = 5;
    const CSV_COMMUNE               = 6;
    const CSV_CODE_INSEE            = 7;
    const CSV_CODE_POSTAL           = 8;
    const CSV_CANTON                = 9;
    const CSV_ACTIF                 = 10;
    const CSV_ATTRIBUTS             = 11;
    const CSV_TYPE                  = 12;
    const CSV_TEL                   = 13;
    const CSV_FAX                   = 14;
    const CSV_PORTABLE              = 15;
    const CSV_EMAIL                 = 16;
    const CSV_WEB                   = 17;
    const CSV_DATE_ARCHIVAGE        = 18;
    const CSV_SIREN                 = 19;
    const CSV_SIRET                 = 20;

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
        $this->name = 'Etablissements';
        $this->briefDescription = 'Import des etablissements';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $etablissement = null;
        foreach(file($arguments['file']) as $line) {
            $line = str_replace("\n", "", $line);

            if(preg_match("/^#/", $line)) {
                
                continue;
            }

            $data = str_getcsv($line, ';');

            if(!$etablissement && $data[self::CSV_TYPE_LIGNE] != "1.CVI ") {

                continue;
            }

            if($etablissement && $etablissement->cvi != $data[self::CSV_CVI]) {
                $this->saveEtablissement($etablissement);
                $etablissement = null;
            }

            if(!$etablissement) {
                $etablissement = $this->getEtablissement($data);
            }

            try{
                $this->importLine($data, $etablissement);
            } catch (Exception $e) {

                echo sprintf("ERROR;%s;#LINE;%s\n", $e->getMessage(), $line);
                $etablissement = null;
                continue;
            }
        }

        if($etablissement) {
            $this->saveEtablissement($etablissement);
        }
    }

    protected function saveEtablissement($etablissement) {

        if(count($etablissement->familles) == 0) {
            echo sprintf("ERROR;%s;#LINE;%s\n", "Aucune famille", $etablissement->cvi);
            return;
        }

        if(!$etablissement->familles->exist(EtablissementClient::FAMILLE_VINIFICATEUR)) {
            echo sprintf("ERROR;%s;#LINE;%s\n", "Etablissement non vinificateur ignoré", $etablissement->cvi);
            return;
        }

        $etablissement->constructId();
        if($etablissement->isNew()) {
            echo sprintf("SUCCESS;%s;%s\n", "Création", $etablissement->_id);
        } else {
            echo sprintf("SUCCESS;%s;%s\n", "Mise à jour", $etablissement->_id);
        }
        $etablissement->save();
        $etablissement = null;
    }

    protected function getEtablissement($data) {
        $etablissement = EtablissementClient::getInstance()->createOrFind($data[self::CSV_CVI]);

        return $etablissement;
    }

    protected function importLine($data, $etablissement) {
        if($data[self::CSV_TYPE_LIGNE] == "1.CVI ") {
            
            return $this->importLineCVI($data, $etablissement);
        }

        if($data[self::CSV_TYPE_LIGNE] == "2.CHAI") {
            
            return $this->importLineChai($data, $etablissement);
        }

        if($data[self::CSV_TYPE_LIGNE] == "3.SIRE") {
            
            return $this->importLineSiret($data, $etablissement);
        }

        if($data[self::CSV_TYPE_LIGNE] == "4.COMM") {
            
            return $this->importLineCommunication($data, $etablissement);
        }

        if($data[self::CSV_TYPE_LIGNE] == "6.ATTR") {

            return $this->importLineAttribut($data, $etablissement);
        }
    }

    protected function importLineCVI($data, $etablissement) {
        if($data[self::CSV_ACTIF] != "1") {
            
            //throw new Exception("L'établissement n'est pas actif");
        }

        if(!preg_match("/^[0-9]{10}$/", $data[self::CSV_CVI])) {

            throw new Exception("Le CVI n'est pas au bon format");
        }

        $etablissement->cvi = $data[self::CSV_CVI];
        $etablissement->raison_sociale = $data[self::CSV_RAISON_SOCIALE];
        $etablissement->nom = $etablissement->raison_sociale;
        $adresse = $this->formatAdresse($data);
        if(!$adresse) {
            throw new sfException("Adresse vide");
        }
        $etablissement->adresse = $adresse;
        $etablissement->commune = $data[self::CSV_COMMUNE];
        $etablissement->code_postal = $data[self::CSV_CODE_POSTAL];
        $etablissement->code_insee = $data[self::CSV_CODE_INSEE];
        $etablissement->chais = array();
        $etablissement->familles = array();
    }

    protected function importLineChai($data, $etablissement) {
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

    protected function importLineSiret($data, $etablissement) {
        if($data[self::CSV_DATE_ARCHIVAGE]) {

            throw new Exception("Etablissement archivé");
        }
        $siret = $data[self::CSV_SIRET];
        if(!$data[self::CSV_SIRET] && $data[self::CSV_SIREN]) {
            $siret = $data[self::CSV_SIREN];
        }

        $siret = str_replace(" ", "", $siret);

        if($siret && !preg_match("/^[0-9]+$/", $siret)) {

            throw new Exception("Le SIRET n'est pas au bon format");
        }

        $etablissement->siret = ($siret) ? $siret : null;
    }

    protected function importLineCommunication($data, $etablissement) {

        $telephone = $this->formatPhone($data[self::CSV_TEL]);
        $mobile = $this->formatPhone($data[self::CSV_PORTABLE]);
        $fax = $this->formatPhone($data[self::CSV_FAX]);  

        if($data[self::CSV_TYPE] == "bur") {
            $etablissement->telephone_bureau = $telephone;
            $etablissement->telephone_mobile = $mobile;
            $etablissement->fax = $fax;  
        }

        if($data[self::CSV_TYPE] != "bur") {
            if($telephone) {
                $etablissement->telephone_prive = $telephone;
            }

            if($telephone && !$etablissement->telephone_bureau) {
                $etablissement->telephone_bureau = $telephone;
            }

            if($fax && !$etablissement->fax) {
                $etablissement->fax = $fax;
            }

            if($mobile) {
                $etablissement->telephone_prive = $mobile;
            }

            if($mobile && !$etablissement->telephone_mobile) {
                $etablissement->telephone_mobile = $mobile;
            }
        }

        $email = trim($data[self::CSV_EMAIL]);
        if($email && !preg_match("/^[a-zA-Z0-9\._-]+@[a-zA-Z0-9\._-]+$/", $email)) {
            throw new Exception("L'email n'est pas au bon format"); 
        }

        $email = ($email) ? $email : null;

        if($data[self::CSV_TYPE] == "bur") {
            $etablissement->email = $email;  
        }

        if($email && !$etablissement->email && $data[self::CSV_TYPE] == "pri") {
            $etablissement->email = $email;  
        }
    }

    protected function importLineAttribut($data, $etablissement) {
        if(preg_match("/Vinificateur/", $data[self::CSV_ATTRIBUTS])) {
            $etablissement->familles->add(EtablissementClient::FAMILLE_VINIFICATEUR);
        }

        if(preg_match("/Producteur/", $data[self::CSV_ATTRIBUTS])) {
            $etablissement->familles->add(EtablissementClient::FAMILLE_PRODUCTEUR);
        }

        if(preg_match("/Distillation/", $data[self::CSV_ATTRIBUTS])) {
            $etablissement->familles->add(EtablissementClient::FAMILLE_DISTILLATEUR);
        }
    }

    protected function formatAdresse($data) {

        return trim(preg_replace("/[ ]+/", " ", sprintf("%s %s %s", $data[self::CSV_ADRESSE_1], $data[self::CSV_ADRESSE_2], $data[self::CSV_ADRESSE_3])));
    }

    protected function formatPhone($numero) {
        $numero = trim(preg_replace("/[_\.]+/", "", $numero));
        if($numero && !preg_match("/^[0-9]{10}$/", $numero)) {
            throw new Exception("Téléphone invalide"); 
        }

        return ($numero) ? $numero : null;
    }

}
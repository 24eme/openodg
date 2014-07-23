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
    const CSV_CODE_POSTAL           = 7;
    const CSV_CANTON                = 8;
    const CSV_ACTIF                 = 9;
    const CSV_ATTRIBUTS             = 10;
    const CSV_TYPE                  = 11;
    const CSV_TEL                   = 12;
    const CSV_FAX                   = 13;
    const CSV_PORTABLE              = 14;
    const CSV_EMAIL                 = 15;
    const CSV_WEB                   = 16;
    const CSV_DATE_CHANGEMENT_ACTIF = 17;

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
        if($etablissement->isNew()) {
            echo sprintf("SUCCESS;%s;%s\n", "Création", $etablissement->cvi);
        } else {
            echo sprintf("SUCCESS;%s;%s\n", "Mise à jour", $etablissement->cvi);
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

        if($data[self::CSV_TYPE_LIGNE] == "3.COMM") {
            
            return $this->importLineCommunication($data, $etablissement);
        }
    }

    protected function importLineCVI($data, $etablissement) {
        if($data[self::CSV_ACTIF] != "1") {
            
            throw new Exception("L'établissement n'est pas actif");
        }

        if(!preg_match("/^[0-9]{10}$/", $data[self::CSV_CVI])) {

            throw new Exception("Le CVI n'est pas au bon format");
        }

        $etablissement->cvi = $data[self::CSV_CVI];
        $etablissement->raison_sociale = $data[self::CSV_RAISON_SOCIALE];
        $etablissement->adresse = preg_replace("/[ ]+/", " ", sprintf("%s %s %s", $data[self::CSV_ADRESSE_1], $data[self::CSV_ADRESSE_2], $data[self::CSV_ADRESSE_3]));
        $etablissement->code_postal = $data[self::CSV_CODE_POSTAL];
        $etablissement->commune = "";

    }

    protected function importLineChai($data, $etablissement) {

    }

    protected function importLineCommunication($data, $etablissement) {

        $etablissement->telephone = ($data[self::CSV_TEL]) ? $data[self::CSV_TEL] : $data[self::CSV_PORTABLE];
        $etablissement->fax = $data[self::CSV_FAX];
        $etablissement->email = $data[self::CSV_EMAIL];
    }

}
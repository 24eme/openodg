<?php

class importEtablissementsTask extends sfBaseTask
{

    const CSV_NUMERO_INTERNE        = 0;
    const CSV_ORGANISME             = 1;
    const CSV_CVI                   = 2;
    const CSV_RAISON_SOCIALE        = 3;
    const CSV_ADRESSE_1             = 4;
    const CSV_ADRESSE_2             = 5;
    const CSV_ADRESSE_3             = 6;
    const CSV_PAYS                  = 7;
    const CSV_COMMUNE               = 8;
    const CSV_CODE_POSTAL           = 9;
    const CSV_CANTON                = 10;
    const CSV_CEDEX                 = 11;
    const CSV_DATE_MODIF            = 12;
    const CSV_USER_MODIF            = 13;
    const CSV_DATE_ARCHIVAGE        = 14;
    const CSV_USER_ARCHIVAGE        = 15;
    const CSV_DATE_SUPPRESSION      = 16;
    const CSV_USER_SUPPRESSION      = 17;
    const CSV_ACTIF                 = 18;
    const CSV_DATE_CHANGEMENT_ACTIF = 19;

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

        foreach(file($arguments['file']) as $line) {
            $line = str_replace("\n", "", $line);
            $data = str_getcsv($line, ';');

            if($data[self::CSV_ACTIF] != "1") {

                echo sprintf("WARNING;%s;%s;#LINE;%s\n", "L'établissement n'est pas actif", $data[self::CSV_ACTIF], $line);
                continue;
            }

            if(!preg_match("/[0-9]{10}/", $data[self::CSV_CVI])) {

                echo sprintf("ERROR;%s;%s;#LINE;%s\n", "Le CVI n'est pas au bon format", $data[self::CSV_CVI], $line);
                continue;
            }

            $etab = EtablissementClient::getInstance()->createOrFind($data[self::CSV_CVI]);
            $etab->cvi = $data[self::CSV_CVI];
            $etab->raison_sociale = $data[self::CSV_RAISON_SOCIALE];
            $etab->siege->adresse = $data[self::CSV_ADRESSE_1];
            $etab->siege->code_postal = $data[self::CSV_CODE_POSTAL];
            $etab->siege->commune = "";

            if($etab->isNew()) {
                echo sprintf("SUCCESS;%s;%s;#LINE;%s\n", "Création", $data[self::CSV_CVI], $line);
            } else {
                echo sprintf("SUCCESS;%s;%s;#LINE;%s\n", "Mise à jour", $data[self::CSV_CVI], $line);
            }
            $etab->save();
        }
    }

}
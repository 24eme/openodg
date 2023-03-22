<?php

class importEtablissementsAssvasTask extends sfBaseTask
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
        $this->name = 'etablissements-assvas';
        $this->briefDescription = 'Import des etablissements';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $csv = fopen($arguments['file'], 'r');
        while(($line = fgetcsv($csv, 0, ';')) !== false) {
            print_r($line);
        }
    }

}

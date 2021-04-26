<?php

class ImportFacturesIATask extends importOperateurIACsvTask
{

    const CSV_FACTURE_NUM_DOSSIER = 0;
    const CSV_FACTURE_NUM_FACTURE = 1;
    const CSV_FACTURE_DATE_FACTURE = 2;
    const CSV_FACTURE_CAMPAGNE = 3;
    const CSV_FACTURE_TYPE = 4;
    const CSV_FACTURE_RAISON_SOCIALE = 5;
    const CSV_FACTURE_CODE_POSTAL = 6;
    const CSV_FACTURE_VILLE = 7;
    const CSV_FACTURE_IGP = 8;
    const CSV_FACTURE_VOLUME = 9;
    const CSV_FACTURE_MONTANT_FACTURE = 10;
    const CSV_FACTURE_TOTAL_REGLE = 11;


    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('csv', sfCommandArgument::REQUIRED, "Fichier csv pour l'import"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'import';
        $this->name = 'factures-ia';
        $this->briefDescription = 'Import des factures (via un csv)';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        sfContext::createInstance($this->configuration);
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        foreach(file($arguments['csv']) as $line) {
            $line = str_replace("\n", "", $line);
            $data = str_getcsv($line, ';');
            if (!$data || $data[self::CSV_FACTURE_RAISON_SOCIALE] == "Raison Sociale") {
              continue;
            }
            $etablissement = $this->identifyEtablissement($data[self::CSV_FACTURE_RAISON_SOCIALE], null, $data[self::CSV_FACTURE_CODE_POSTAL]);
            if (!$etablissement) {
               echo "WARNING;établissement non trouvé ".$data[self::CSV_FACTURE_RAISON_SOCIALE].";pas d'import;$line\n";
               continue;
            }
            $date = strtok($data[self::CSV_FACTURE_DATE_FACTURE], '/');
            $date = strtok('/').'-'.$date;
            $date = strtok('/').'-'.$date;
            $mouvements = array();
            $facture = FactureClient::getInstance()->createEmptyDoc($etablissement, $date, "facture importée", strtoupper($options['application']));
        }
    }

}

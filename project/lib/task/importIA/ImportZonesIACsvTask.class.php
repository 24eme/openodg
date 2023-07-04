<?php

class ImportZonesIACsvTask extends importOperateurIAAOCCsvTask
{
    const CSV_SECTEUR = 0;
    const CSV_RAISON_SOCIALE_OPERATEUR = 1;

    protected $etablissements = null;
    protected $etablissementsCache = array();

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
        $this->name = 'zones-ia';
        $this->briefDescription = 'Import des opérateurs (via un csv)';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        foreach(file($arguments['csv']) as $line) {
            $data = str_getcsv($line, ";");

            $etablissement = $this->identifyEtablissement($data[self::CSV_RAISON_SOCIALE_OPERATEUR]);

            if(!$etablissement) {
                echo "Établissement non trouvé : ".$data[self::CSV_RAISON_SOCIALE_OPERATEUR]."\n";
                continue;
            }

            $etablissement = EtablissementClient::getInstance()->find($etablissement->_id);
            $etablissement->region = $data[self::CSV_SECTEUR];
            $etablissement->save();

            echo $etablissement->_id."\n";
        }
    }
}

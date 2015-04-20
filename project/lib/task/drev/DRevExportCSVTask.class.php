<?php

class DRevExportCsvTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "Document id")
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            new sfCommandOption('header', null, sfCommandOption::PARAMETER_REQUIRED, 'Add header in CSV', true),
        ));

        $this->namespace = 'drev';
        $this->name = 'export-csv';
        $this->briefDescription = "Export CSV d'un DRev";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        for($i=0;$i < 1000;$i++) {
            $drev = DRevClient::getInstance()->find($arguments['doc_id']);
            
            if(!$drev) {
                
                return;
            }

            if(!$drev->validation) {

                return;
            }

            $export = new ExportDRevCSV($drev, $options["header"]);

            echo $export->export();
        }
    }
}
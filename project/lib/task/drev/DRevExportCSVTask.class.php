<?php

class DRevExportCsvTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('docs_id', sfCommandArgument::IS_ARRAY, "Documents id")
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

        if($options["header"]) {
                echo ExportDRevCSV::getHeaderCsv();
        }

        foreach($arguments['docs_id'] as $doc_id) {
            $drev = DRevClient::getInstance()->find($doc_id);
            
            if(!$drev) {
                
                continue;
            }

            if(!$drev->validation) {

                continue;
            }


            $export = new ExportDRevCSV($drev, false);
         
            echo $export->export();
        }
    }
}
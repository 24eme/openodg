<?php

class ParcellaireExportCsvTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "Document id")
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            new sfCommandOption('header', null, sfCommandOption::PARAMETER_REQUIRED, 'Add header in CSV', true),
        ));

        $this->namespace = 'parcellaire';
        $this->name = 'export-csv';
        $this->briefDescription = "Export CSV d'un parcellaire";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $p = ParcellaireClient::getInstance()->find($arguments['doc_id']);

        if(!$p) {
            
            return;
        }

        if(!$p->validation) {

            return;
        }

        $export = new ExportParcellaireCSV($p, $options["header"]);

        echo $export->export();
    }
}
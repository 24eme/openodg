<?php

class DeclarationExportCsvTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "Document id"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            new sfCommandOption('header', null, sfCommandOption::PARAMETER_REQUIRED, 'Add header in CSV', true),
        ));

        $this->namespace = 'declaration';
        $this->name = 'export-csv';
        $this->briefDescription = "Export CSV d'une declaration";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $doc = DeclarationClient::getInstance()->find($arguments['doc_id']);

        if(!$doc) {
                
            throw new sfException(sprintf("Document %s introuvable", $arguments['doc_id']));
        }

        if($options["header"]) {
            $className = DeclarationClient::getInstance()->getExportCsvClassName($doc->type);
            echo $className::getHeaderCsv();
        }

        $export = DeclarationClient::getInstance()->getExportCsvObject($doc, false);
         
        echo $export->export();
    }
}
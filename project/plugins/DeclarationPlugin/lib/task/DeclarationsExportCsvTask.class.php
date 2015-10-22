<?php

class DeclarationsExportCsvTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('type', sfCommandArgument::REQUIRED, "Type du document"),
            new sfCommandArgument('campagne', sfCommandArgument::REQUIRED, "Campagne"),
            new sfCommandArgument('validation', sfCommandArgument::OPTIONAL, "Document validé uniquement", true),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            new sfCommandOption('header', null, sfCommandOption::PARAMETER_REQUIRED, 'Add header in CSV', true),
        ));

        $this->namespace = 'declarations';
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

        if($options["header"]) {
            $className = DeclarationClient::getInstance()->getExportCsvClassName($arguments['type']);
            echo $className::getHeaderCsv();
        }

        $ids = DeclarationClient::getInstance()->getIds($arguments['type'], $arguments['campagne']);

        foreach($ids as $id) {
            $doc = DeclarationClient::getInstance()->find($id);
            $export = DeclarationClient::getInstance()->getExportCsvObject($doc, false);

            if($arguments['validation'] && $doc->exist('validation') && !$doc->validation) {               
                continue;
            }

            echo $export->export();
        }

         
    }
}
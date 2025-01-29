<?php

class ExportEngagementsCSVTask extends sfBaseTask{

    protected function configure()
    {

        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::OPTIONAL, "Id du document"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace        = 'declaration';
        $this->name             = 'engagements';
        $this->briefDescription = 'Export engagements/autorisations opérateur';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $context = sfContext::createInstance($this->configuration);

        if (isset($arguments['doc_id']) && $arguments['doc_id']) {
            $doc = acCouchdbManager::getClient()->find($arguments['doc_id']);
            $export = new ExportDocEngagementCSV($doc);

            $csv = $export->exportForOneDoc();

            print($csv);
            return;
        }

        $export = new ExportDocEngagementCSV();
        $csv = $export->export();
        print($csv);
        return;
    }
}


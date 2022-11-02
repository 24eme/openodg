<?php

class ExportEngagementsCSVTask extends sfBaseTask{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('drev_id', sfCommandArgument::REQUIRED, "Id de la DRev"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace        = 'drev';
        $this->name             = 'engagements';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $context = sfContext::createInstance($this->configuration);

        $drev = DRevClient::getInstance()->find($arguments['drev_id']);

        if(!$drev) {
            die("DREV ".$arguments['drev_id']." non trouvée");
        }

        $export = new ExportDRevEngagementCSV($drev);
        $csv = $export->export();

        print($csv);
    }
}


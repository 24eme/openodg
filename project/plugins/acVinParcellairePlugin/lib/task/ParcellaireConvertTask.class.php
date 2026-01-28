<?php

class ParcellaireConvertTask extends sfBaseTask
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
            new sfCommandOption('debug', null, sfCommandOption::PARAMETER_REQUIRED, 'Debug', false),
        ));

        $this->namespace = 'parcellaire';
        $this->name = 'convert';
        $this->briefDescription = "Permet de changer les clé de CDR à CDP";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
        $context = sfContext::createInstance($this->configuration);

        $parcellaire = ParcellaireClient::getInstance()->find($arguments['doc_id']);

        ParcellaireClient::getInstance()->loadParcellaireCSV($parcellaire, $options['debug']);
    }

}

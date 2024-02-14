<?php

class DRevCreateEmptyTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('identifiant', sfCommandArgument::REQUIRED, "Document id"),
            new sfCommandArgument('periode', sfCommandArgument::REQUIRED, "Document id"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'drev';
        $this->name = 'create-empty';
        $this->briefDescription = "Sauvegarde de la DRev";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();


        $drev = DRevClient::getInstance()->createDoc($arguments['identifiant'], $arguments['periode']);
        $drev->validation = date('Y-m-d');
        $drev->validation_odg = date('Y-m-d');
        $drev->save();

        echo sprintf("SUCCESS;La DRev vide créée;%s\n", $drev->_id);
    }
}

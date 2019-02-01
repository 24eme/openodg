<?php

class DRevRegenerateMouvementsTask extends sfBaseTask
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
        ));

        $this->namespace = 'drev';
        $this->name = 'regenerate-mouvements';
        $this->briefDescription = "Regénère les mouvements de la DRev";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();


        $drev = DRevClient::getInstance()->find($arguments['doc_id']);
        $drev->mouvements = null;
        $drev->generateMouvements();
        $drev->save();
        echo sprintf("SUCCESS;La DRev a bien des nouveaux mouvements ;%s\n", $drev->_id);
    }
}

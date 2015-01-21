<?php

class CompteUpdatePositionTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "Compte doc id"),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'compte';
        $this->name = 'update-position';
        $this->briefDescription = "Mise à jour de la lattitude/longitude d'un compte";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $compte = CompteClient::getInstance()->find($arguments['doc_id']);

        if(!$compte) {
            echo sprintf("ERROR;COMPTE %s inexistant", $arguments['doc_id']);
            return;
        }

        if(!$compte->updateCoordonneesLongLat()) {
            echo sprintf("ERROR;COMPTE %s position non trouvée", $arguments['doc_id']);
            return;
        }

        $compte->save();
        echo sprintf("SUCCESS;COMPTE %s position update", $arguments['doc_id']);
    }
}
<?php

class FixCompteReSaveTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "Document ID")
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'fix';
        $this->name = 'compte-resave';
        $this->briefDescription = "Re sauve les comptes afin de resynchroniser des informations manquantes";
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
            echo sprintf("ERROR;Compte introuvable;%s\n", $compte->_id);
            return;
        }

        $compte->updateCoordonneesLongLat();
        $compte->save();

        echo sprintf("UPDATE;%s;%s;%s\n", $compte->_id, $compte->region, $compte->societe_informations->siret);
    }
}

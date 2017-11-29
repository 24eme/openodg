<?php

class CompteArchivageTask extends sfBaseTask
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
        $this->name = 'archivage';
        $this->briefDescription = "Permet d'archiver un compte";
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
            echo sprintf("ERROR;COMPTE %s inexistant\n", $arguments['doc_id']);
            return;
        }


        if($compte->date_archivage && $compte->statut == CompteClient::STATUT_INACTIF) {
            echo sprintf("WARNING;COMPTE %s déjà archivé\n", $arguments['doc_id']);

            return;
        }

        $compte->archiver();

        $compte->save();
        echo sprintf("SUCCESS;COMPTE %s archivé\n", $arguments['doc_id']);
    }
}
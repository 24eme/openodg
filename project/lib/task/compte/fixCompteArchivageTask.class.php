<?php

class FixCompteArchivageTask extends sfBaseTask
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
        $this->name = 'compte-archivage';
        $this->briefDescription = "Fixe du numéro d'archivage des comptes";
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
            echo sprintf("ERROR;Compte introuvable;%s\n", $p->_id);
            return;
        }

        if($compte->numero_archive) {
            return;
        }

        if(!$compte->identifiant_interne) {

            return;
        }

        $compte->numero_archive = $compte->identifiant_interne;
        $compte->campagne_archive = Compte::CAMPAGNE_ARCHIVE;

        $compte->save();

        echo sprintf("UPDATE;%s;%s;%s:%s\n", $compte->_id, $compte->identifiant_interne, $compte->campagne_archive, $compte->numero_archive);
    }
}
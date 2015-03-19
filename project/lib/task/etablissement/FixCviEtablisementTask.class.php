<?php

class FixCviEtablisementTask extends sfBaseTask
{

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('doc_id', sfCommandArgument::REQUIRED, "")
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'declaration'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
        ));

        $this->namespace = 'fix';
        $this->name = 'EtablissementCvi';
        $this->briefDescription = "Corrige les cvis avec un espace à la fin";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $e = EtablissementClient::getInstance()->find($arguments['doc_id']);

        if(!$e) {
            echo sprintf("ERROR;Etablissement %s inexistant\n", $arguments['doc_id']);
            return;
        }

        if(trim($e->identifiant) == $e->identifiant) {
            echo sprintf("WARNING;Etablissement déjà corrigé %s\n", $e->_id);
            return;
        }

        $e->identifiant = trim($e->identifiant);
        $e->save();

        echo sprintf("SUCCESS;Etablissement corrigé %s\n", $e->_id);
    }
}
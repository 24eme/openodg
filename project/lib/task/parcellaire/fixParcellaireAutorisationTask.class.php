<?php

class FixParcellaireAutorisationTask extends sfBaseTask
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
        $this->name = 'parcellaire-autorisation';
        $this->briefDescription = "Corrige l'autorisation de transmission du parcellaire'";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $p = ParcellaireClient::getInstance()->find($arguments['doc_id']);
        if(!$p) {
            return;
        }
        if(!$p->validation) {
            return;
        }
        if($p->isPapier()) {
            return;
        }
        if($p->autorisation_acheteur) {
            return;
        }
        $p->autorisation_acheteur = true;
        $p->save();

        echo sprintf("CORRIGÉE;%s\n", $p->_id);
    }
}
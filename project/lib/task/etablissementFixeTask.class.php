<?php

class etablissementFixeTask extends sfBaseTask
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

        $this->namespace = 'etablissement';
        $this->name = 'fixe';
        $this->briefDescription = "Fixe";
        $this->detailedDescription = <<<EOF
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        $etablissement = EtablissementClient::getInstance()->find($arguments['doc_id']." ", acCouchdbClient::HYDRATE_JSON);

        if(!$etablissement) {
            echo "ERROR;Pas trouvé ".$arguments['doc_id']."\n";
            return;
        }

        $etablissement_corrige = clone $etablissement;
        $etablissement_corrige->_id = trim($etablissement->_id);
        unset($etablissement_corrige->_rev);

        try {
        acCouchdbManager::getClient()->deleteDoc($etablissement);
        acCouchdbManager::getClient()->storeDoc($etablissement_corrige);
        } catch (Exception $e) {
            echo "ERROR;".$e->getMessage()." ".$etablissement_corrige->_id."\n";
            return;
        } 
        echo "SUCCESS;Etablissement fixed ".$etablissement_corrige->_id."\n";
    }
}
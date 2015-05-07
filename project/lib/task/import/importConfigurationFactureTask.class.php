<?php

class importConfigurationFactureTask extends sfBaseTask {

    protected function configure() {
        // // add your own arguments here
        $this->addArguments(array(
            new sfCommandArgument('campagne', sfCommandArgument::REQUIRED, 'Campagne'),
        ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            // add your own options here
            new sfCommandOption('import', null, sfCommandOption::PARAMETER_REQUIRED, 'import type [couchdb|stdout]', 'couchdb'),
            new sfCommandOption('removedb', null, sfCommandOption::PARAMETER_REQUIRED, '= yes if remove the db debore import [yes|no]', 'no'),
        ));

        $this->namespace = 'import';
        $this->name = 'ConfigurationFacture';
        $this->briefDescription = 'import configuration facture';
        $this->detailedDescription = <<<EOF
The [import|INFO] task does things.
Call it with:

  [php symfony import|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array()) {
        ini_set('memory_limit', '512M');
        set_time_limit('3600');
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

        if ($options['removedb'] == 'yes' && $options['import'] == 'couchdb') {
            if (acCouchdbManager::getClient()->databaseExists()) {
                acCouchdbManager::getClient()->deleteDatabase();
            }
            acCouchdbManager::getClient()->createDatabase();
        }

        /*
         * Parsing de la configuration Civa
         */
        $configurationAocJson = file_get_contents(sfConfig::get('sf_data_dir') . '/import/configuration/factureaoc'.$arguments['campagne'].'.json');
        $configurationMarcJson = file_get_contents(sfConfig::get('sf_data_dir') . '/import/configuration/facturemarc'.$arguments['campagne'].'.json');

        if (!$configurationAocJson) {
            throw new sfCommandException("Le fichier de configuration facture aoc n'est pas existant dans l'arborescence " . sfConfig::get('sf_data_dir') . '/import/configuration/');
        }
    	if (!$configurationMarcJson) {
            throw new sfCommandException("Le fichier de configuration facture marc n'est pas existant dans l'arborescence " . sfConfig::get('sf_data_dir') . '/import/configuration/');
        }
        $configurationAocJson = json_decode($configurationAocJson);
        $configurationMarcJson = json_decode($configurationMarcJson);

        if ($options['import'] == 'couchdb') {

            if ($doc = acCouchdbManager::getClient()->find($configurationAocJson->_id, acCouchdbClient::HYDRATE_JSON)) {
                acCouchdbManager::getClient()->deleteDoc($doc);
            }
            $doc = acCouchdbManager::getClient()->createDocumentFromData($configurationAocJson);
            $doc->save();
            $this->logSection('configuration', 'Configuration aoc importée avec succès');
            
            if ($doc = acCouchdbManager::getClient()->find($configurationMarcJson->_id, acCouchdbClient::HYDRATE_JSON)) {
                acCouchdbManager::getClient()->deleteDoc($doc);
            }
            $doc = acCouchdbManager::getClient()->createDocumentFromData($configurationMarcJson);
            $doc->save();
            $this->logSection('configuration', 'Configuration marc importée avec succès');
            
        } else {
            echo '{"docs":';
            echo json_encode($configurationAocJson);
            echo '}';
            echo "\n";
            echo '{"docs":';
            echo json_encode($configurationMarcJson);
            echo '}';
            echo "\n";
        }
    }
}

<?php

class importConfiguration2013Task extends sfBaseTask
{

    protected $cepage_order = array("CH", "SY", "AU", "PB", "PI", "ED", "RI", "PG", "MU", "MO", "GW");
    
    protected function configure()
    {
        // // add your own arguments here
        // $this->addArguments(array(
        //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
        // ));

        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'default'),
            // add your own options here
            new sfCommandOption('import', null, sfCommandOption::PARAMETER_REQUIRED, 'import type [couchdb|stdout]', 'couchdb'),
            new sfCommandOption('removedb', null, sfCommandOption::PARAMETER_REQUIRED, '= yes if remove the db debore import [yes|no]', 'no'),
        ));

        $this->namespace = 'import';
        $this->name = 'Configuration2013';
        $this->briefDescription = 'import configuration 2013';
        $this->detailedDescription = <<<EOF
The [import|INFO] task does things.
Call it with:

  [php symfony import|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
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
        
        $configurationJson = file_get_contents(sfConfig::get('sf_data_dir') . '/import/configuration/2013.json');
        
        if (!$configurationJson) {
        	throw new sfCommandException("Le fichier de configuration 2013 n'est pas existant dans l'arborescence ".sfConfig::get('sf_data_dir') . '/import/configuration/');
        }
        $configurationJson = json_decode($configurationJson);
        
        $certifications = $configurationJson->declaration->certification;
        unset($configurationJson->declaration->certification);
        
        $configurationJson->declaration->certification->genre->appellation_ALSACEBLANC = $certifications->genre->appellation_ALSACEBLANC;
        $configurationJson->declaration->certification->genre->appellation_PINOTNOIR = $certifications->genre->appellation_PINOTNOIR;
        $configurationJson->declaration->certification->genre->appellation_PINOTNOIRROUGE = $certifications->genre->appellation_PINOTNOIRROUGE;
        $configurationJson->declaration->certification->genre->appellation_COMMUNALE = $certifications->genre->appellation_COMMUNALE;
        $configurationJson->declaration->certification->genre->appellation_LIEUDIT = $certifications->genre->appellation_LIEUDIT;
        $configurationJson->declaration->certification->genre->appellation_GRDCRU = $certifications->genre->appellation_GRDCRU;
        $configurationJson->declaration->certification->genre->appellation_CREMANT = $certifications->genre->appellation_CREMANT;
        
        $grdCruCepages = $this->getCepages($configurationJson->declaration->certification->genre->appellation_GRDCRU);
        $configurationJson->declaration->certification->genre->appellation_GRDCRU->mention->lieu->couleur = $grdCruCepages;
        
        $communaleBlancCepages = $this->getCepages($configurationJson->declaration->certification->genre->appellation_COMMUNALE, 'couleurBlanc');
        $communaleRougeCepages = $this->getCepages($configurationJson->declaration->certification->genre->appellation_COMMUNALE, 'couleurRouge');
        $configurationJson->declaration->certification->genre->appellation_COMMUNALE->mention->lieu->couleurBlanc = $communaleBlancCepages;
        $configurationJson->declaration->certification->genre->appellation_COMMUNALE->mention->lieu->couleurRouge = $communaleRougeCepages;
        
    	if ($options['import'] == 'couchdb') {
    		
    		if ($doc = acCouchdbManager::getClient()->find($configurationJson->_id, acCouchdbClient::HYDRATE_JSON)) {
            	acCouchdbManager::getClient()->deleteDoc($doc);
            }
            $doc = acCouchdbManager::getClient()->createDocumentFromData($configurationJson);
        	$doc->save();
        }
        

        echo '{"docs":';
        echo json_encode($configurationJson);
        echo '}';
        echo "\n";

    }
    
    protected function getCepages($appellation, $noeudCouleur = 'couleur') 
    {
    	$cepages = new stdClass();
    	foreach ($appellation as $m => $mention) {
    		if (preg_match('/^mention/', $m)) {
    			foreach ($mention as $l => $lieu) {
    				if (preg_match('/^lieu/', $l)) {
    					foreach ($lieu as $co => $couleur) {
    						if (preg_match('/^'.$noeudCouleur.'/', $co)) {
    							foreach ($couleur as $c => $cepage) {
    								if (preg_match('/^cepage/', $c)) {
    									if (!isset($cepages->{$c})) {
    										$cepages->{$c} = $cepage;
    									}
    								}		
    							}		
    						}
    					}	
    				}	
    			}		
    		}
    	}
    	return $cepages;
    }

}
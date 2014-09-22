<?php

class ConfigurationClient extends acCouchdbClient {
    private static $configuration = array();
    private static $current = null;
  
    public static function getInstance() {
          
        return acCouchdbManager::getClient("CONFIGURATION");
    }
  
    public static function getConfiguration($campagne = '') {
        if (!$campagne) {
            if (!self::$current)
                self::$current = CurrentClient::getCurrent();
            $campagne = self::$current->campagne;
        }
        
        if (!isset(self::$configuration[$campagne])) {
            self::$configuration[$campagne] = CacheFunction::cache('model', array(acCouchdbManager::getClient(), 'find'), array('CONFIGURATION-'.$campagne));
        }

        if (self::$configuration[$campagne]->exist('virtual') && self::$configuration[$campagne]->virtual != $campagne) {
            self::$configuration[$campagne] = self::getConfiguration(self::$configuration[$campagne]->virtual);
        }

        return self::$configuration[$campagne];
    }

    public function retrieveConfiguration($campagne = '') {
        
        return self::getConfiguration($campagne);
    }

    public function getCampagneManager() {

        return new CampagneManager('08-01');
    }
}

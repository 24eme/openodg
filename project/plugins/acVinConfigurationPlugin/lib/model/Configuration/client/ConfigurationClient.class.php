<?php

class ConfigurationClient extends acCouchdbClient {
  private static $configuration = array();
  private static $current = null;
  
    const CAMPAGNE_DATE_DEBUT = '%s-08-01';
    const CAMPAGNE_DATE_FIN = '%s-07-31';

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
  
    public function buildCampagne($date) {

        return sprintf('%s-%s', date('Y', strtotime($this->buildDateDebutCampagne($date))), date('Y', strtotime($this->buildDateFinCampagne($date))));
    }
    
        public function buildDateDebutCampagne($date) {
        $annee = date('Y', strtotime($date));
        if(date('m', strtotime($date)) < 8) {
            $annee -= 1;
        }

        return sprintf(self::CAMPAGNE_DATE_DEBUT, $annee); 
    }

    public function buildDateFinCampagne($date) {

        return sprintf(self::CAMPAGNE_DATE_FIN, date('Y', strtotime($this->buildDateDebutCampagne($date)))+1);
    }
}

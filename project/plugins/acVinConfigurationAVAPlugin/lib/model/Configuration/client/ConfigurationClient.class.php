<?php

// AVA //

class ConfigurationClient extends acCouchdbClient {

    private static $configuration = array();
    private static $current = null;
    protected $campagne_parcellaire_manager = null;

    public static function getInstance() {

        return acCouchdbManager::getClient("CONFIGURATION");
    }

    public static function getConfiguration($campagne = '') {

        if(preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}/', $campagne)) {
            $campagne = self::getInstance()->getCampagneManager()->getCampagneByDate($campagne);
        }

        if (!$campagne) {
            if (!self::$current)
                self::$current = CurrentClient::getCurrent();
            if (self::$current) {
                $campagne = self::$current->campagne;
            }
            if (!$campagne) {
                $campagne = self::getInstance()->getCampagneManager()->getCurrent();
            }
        }
        if ($campagne < 2013) {
            $campagne = '2013';
        }
        $campagne++;
        while (!isset(self::$configuration[$campagne]) || ! self::$configuration[$campagne]) {
            $campagne--;
            if(!isset(self::$configuration[$campagne])) {
                self::$configuration[$campagne] = CacheFunction::cache('model', array(acCouchdbManager::getClient(), 'find'), array('CONFIGURATION-' . $campagne));
            }
        }

        if (self::$configuration[$campagne]->exist('virtual') && self::$configuration[$campagne]->virtual != $campagne) {
            self::$configuration[$campagne] = self::getConfiguration(self::$configuration[$campagne]->virtual);
        }

        return self::$configuration[$campagne];
    }

    public function retrieveConfiguration($campagne = '') {

        return $this->getConfiguration($campagne);
    }

    public function getCampagneVinicole() {

        return new CampagneManager('08-01');
    }

    public function getCampagneParcellaire() {
        if(is_null($this->campagne_parcellaire_manager)) {
            $this->campagne_parcellaire_manager = new CampagneManager('03-01');
        }
        return $this->campagne_parcellaire_manager;
    }

    public function getCampagneManager() {

        return new CampagneManager('11-10', CampagneManager::FORMAT_PREMIERE_ANNEE);
    }

    public function buildCampagne($date) {

        return $this->getCampagneVinicole()->getCampagneByDate($date);
    }

	/**
	*
	* @return Current
	*/
	public static function getCurrent() {

		return self::getInstance()->getConfiguration();
	}

    public function getCurrentAnneeRecolte() {

        return $this->getCampagneVinicole()->getCurrentAnneeRecolte();
    }

}

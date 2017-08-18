<?php

class ConfigurationClient extends acCouchdbClient {

    private static $configuration = array();
    private static $current = null;

    protected $countries = null;

    public static function getInstance() {

        return acCouchdbManager::getClient("CONFIGURATION");
    }

    public static function getConfiguration($campagne = '') {
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

        if (!isset(self::$configuration[$campagne])) {
            self::$configuration[$campagne] = CacheFunction::cache('model', array(acCouchdbManager::getClient(), 'find'), array('CONFIGURATION-' . $campagne));
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

        return new CampagneManager('10-23', CampagneManager::FORMAT_PREMIERE_ANNEE);
    }

    public function getCountryList() {
        if(is_null($this->countries)) {
            $destinationChoicesWidget = new sfWidgetFormI18nChoiceCountry(array('culture' => 'fr'));
            $this->countries = $destinationChoicesWidget->getChoices();
            if(class_exists("DRMConfiguration")) {
                DRMConfiguration::getInstance();
                $this->countries = array_merge(array("" => ""), DRMConfiguration::getInstance()->getExportPaysDebut(), $this->countries,    DRMConfiguration::getInstance()->getExportPaysFin());
            }
        }

        return $this->countries;
    }

    public function getCountry($code) {
        $countries = $this->getCountryList();

        return $countries[$code];
    }

    public function findCountryByCode($code) {
        $code = strtoupper($code);

        if(!array_key_exists($code, $this->getCountryList())) {

            return null;
        }

        return $code;

    }

    public function findCountryByLibelle($libelle) {
        $libelleSlugified = KeyInflector::slugify($libelle);
        foreach($this->getCountryList() as $code => $name) {
            if(KeyInflector::slugify($name) == $libelleSlugified) {

                return $code;
            }
        }

        return null;
    }

    public function findCountry($code_or_libelle) {
        $code = $this->findCountryByCode($code_or_libelle);

        if($code) {

            return $code;
        }

        return $this->findCountryByLibelle($code_or_libelle);
    }

}

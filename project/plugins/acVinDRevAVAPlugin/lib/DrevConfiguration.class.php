<?php

/*** AVA ***/

class DRevConfiguration/*** AVA ***/ extends DeclarationConfiguration {

    private static $_instance = null;
    protected $configuration;
    protected $campagneManager = null;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new DRevConfiguration();
        }
        return self::$_instance;
    }

    public function getCampagneDebutMois() {

        return 9;
    }

    public function load() {
        $this->configuration = sfConfig::get('drev_configuration_drev', array());
    }

    public function __construct() {
        if(!sfConfig::has('drev_configuration_drev')) {
			//throw new sfException("La configuration pour les drev n'a pas été défini pour cette application");
		}

        $this->load();
    }

    public function isModuleEnabled() {
        return in_array('drev', sfConfig::get('sf_enabled_modules'));
    }

    public function isRevendicationParLots() {

        return false;
    }

}

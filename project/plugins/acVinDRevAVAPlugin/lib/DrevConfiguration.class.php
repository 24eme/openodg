<?php

/*** AVA ***/

class DRevConfiguration/*** AVA ***/ extends DeclarationConfiguration {

    private static $_instance = null;
    protected $configuration;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new DRevConfiguration();
        }
        return self::$_instance;
    }

    public function getCampagneDebutMois() {

        return 10;
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


    public function isRevendicationParLots() {

        return false;
    }

    public function isSendMailToOperateur() {

        return true;
    }


    public function getModuleName() {

        return 'drev';
    }

    public function hasEmailODGInCopyDisabled() {
        return isset($this->configuration['email_odg_in_copy_disabled']) && boolval($this->configuration['email_odg_in_copy_disabled']);
    }
}

<?php

class DegustationConfiguration {

    private static $_instance = null;
    protected $configuration;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new DegustationConfiguration();
        }
        return self::$_instance;
    }

    public function __construct() {
        if(!sfConfig::has('degustation_configuration_degustation')) {
			throw new sfException("La configuration pour les degustations n'a pas été définie pour cette application");
		}

        $this->configuration = sfConfig::get('degustation_configuration_degustation', array());
    }

    public function getCommissions() {

        return (isset($this->configuration['commissions']))? $this->configuration['commissions'] : array();
    }
}

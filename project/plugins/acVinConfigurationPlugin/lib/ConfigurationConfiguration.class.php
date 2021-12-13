<?php

class ConfigurationConfiguration {

    private static $_instance = null;
    protected $configuration;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new ConfigurationConfiguration();
        }
        return self::$_instance;
    }

    public function __construct() {
        if(!sfConfig::has('configuration_configuration')) {
			throw new sfException("La configuration pour les configuration n'a pas été défini pour cette application");
		}

        $this->configuration = sfConfig::get('configuration_configuration', array());
    }

}

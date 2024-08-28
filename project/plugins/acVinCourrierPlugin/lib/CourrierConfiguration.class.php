<?php

class CourrierConfiguration {

    private static $_instance = null;
    protected $configuration;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new CourrierConfiguration();
        }
        return self::$_instance;
    }

    public function __construct() {
        if(!sfConfig::has('courrier_configuration')) {
			throw new sfException("La configuration pour les configuration n'a pas été défini pour cette application");
		}

        $this->configuration = sfConfig::get('courrier_configuration', array());
    }

}

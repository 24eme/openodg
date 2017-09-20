<?php

class HabilitationConfiguration {

    private static $_instance = null;
    protected $configuration;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new HabilitationConfiguration();
        }
        return self::$_instance;
    }

    public function __construct() {
        if(!sfConfig::has('habilitation_configuration_habilitation')) {
			throw new sfException("La configuration pour les habilitation n'a pas été défini pour cette application");
		}

        $this->configuration = sfConfig::get('habilitation_configuration_habilitation', array());
    }

    public function hasPrelevements() {

        return boolval($this->configuration['prelevements']);
    }

}

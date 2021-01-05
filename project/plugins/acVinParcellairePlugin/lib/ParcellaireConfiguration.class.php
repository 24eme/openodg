<?php

class ParcellaireConfiguration {

    private static $_instance = null;
    protected $configuration;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new ParcellaireConfiguration();
        }
        return self::$_instance;
    }

    public function __construct() {
        if(!sfConfig::has('parcellaire_configuration_parcellaire')) {
			throw new sfException("La configuration pour le parcellaire n'a pas été défini pour cette application");
		}

        $this->configuration = sfConfig::get('parcellaire_configuration_parcellaire', array());
    }

    public function getLimitProduitsConfiguration() {
        if(!isset($this->configuration['limit_produits_configuration'])) {
            return false;
        }

        return $this->configuration['limit_produits_configuration'];
    }

}

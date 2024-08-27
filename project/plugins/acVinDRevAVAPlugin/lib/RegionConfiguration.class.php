<?php

/*** AVA ***/

class RegionConfiguration/*** AVA ***/ {

    private static $_instance = null;
    protected $configuration;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new RegionConfiguration();
        }
        return self::$_instance;
    }

    public function load() {
        $this->configuration = sfConfig::get('region_configuration_region', array());
    }

    public function __construct() {
        if(!sfConfig::has('region_configuration_region')) {
			//throw new sfException("La configuration pour les regions n'a pas été défini pour cette application");
		}

        $this->load();
    }

    public function hasOdgProduits() {
      return false;
    }

    public function getOdgConfigurationItem($odgName, $configurationItem) {
        return null;
    }
}

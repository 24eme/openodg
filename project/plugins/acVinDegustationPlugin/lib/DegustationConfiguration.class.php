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

    public function getColleges() {

        return (isset($this->configuration['colleges']))? $this->configuration['colleges'] : array();
    }

    public function getLibelleCollege($key) {
        $colleges = $this->getColleges();
        return (isset($colleges[$key]))? $colleges[$key] : '';
    }

    public function hasSpecificiteLotPdf(){
      return isset($this->configuration['specificite_lot_pdf']) && boolval($this->configuration['specificite_lot_pdf']);
    }

    public function hasAnonymat4labo()
    {
        return isset($this->configuration['anonymat4labo']) && boolval($this->configuration['anonymat4labo']);
    }
}

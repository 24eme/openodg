<?php

class ChgtDenomConfiguration {

    private static $_instance = null;
    protected $configuration;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new ChgtDenomConfiguration();
        }
        return self::$_instance;
    }

    public function load() {
        $this->configuration = sfConfig::get('chgtdenom_configuration_chgtdenom', array());
    }

    public function __construct() {
        if(!sfConfig::has('chgtdenom_configuration_chgtdenom')) {
            throw new sfException("La configuration pour les changements de dénomination n'a pas été défini pour cette application");
        }

        $this->load();
    }

    public function getSpecificites(){
      if($this->hasSpecificiteLot()){
        return $this->configuration['specificites'];
      }
    }

    public function hasSpecificiteLot(){
      return isset($this->configuration['specificite_lot']) && boolval($this->configuration['specificite_lot']);
    }

}

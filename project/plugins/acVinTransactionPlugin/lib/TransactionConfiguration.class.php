<?php

class TransactionConfiguration {

    private static $_instance = null;
    protected $configuration;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new TransactionConfiguration();
        }
        return self::$_instance;
    }

    public function load() {
        $this->configuration = sfConfig::get('cond_configuration_transaction', array());
    }

    public function __construct() {
        if(!sfConfig::has('cond_configuration_transaction')) {
			throw new sfException("La configuration pour les transaction n'a pas été défini pour cette application");
		}

        $this->load();
    }

    public function hasExploitationSave() {
      return isset($this->configuration['exploitation_save']) && boolval($this->configuration['exploitation_save']);
    }

    public function getOdgRegions(){
      if(!$this->hasOdgProduits()){
        return array();
      }
      return array_keys($this->configuration['odg']);
    }

    public function getOdgRegionInfos($region){
        if(!isset($this->configuration['odg']) || !array_key_exists($region,$this->configuration['odg']) || !isset($this->configuration['odg'][$region]) ){
            return null;
        }
        $odgInfos = array();
        foreach ($this->configuration['odg'][$region] as $key => $value) {
          if(is_string($value) && preg_match("/^%.+%$/",$value)){
            $odgInfos[$key] = sfConfig::get(str_replace("%",'',$value), '');
          }else{
            $odgInfos[$key] = $value;
          }
        }
        return $odgInfos;
    }

    public function hasValidationOdgAuto(){
      return isset($this->configuration['validation_odg']) && $this->configuration['validation_odg'] == 'auto';
    }

    public function hasValidationOdgAdmin(){
      return isset($this->configuration['validation_odg']) && $this->configuration['validation_odg'] == 'admin';
    }

    public function hasValidationOdgRegion(){
      return isset($this->configuration['validation_odg']) && $this->configuration['validation_odg'] == 'region';
    }

    public function hasValidationOdgAutoOrRegion(){
      return $this->hasValidationOdgAuto() || $this->hasValidationOdgRegion();
    }

    public function hasValidationOdgAdminOrRegion(){
      return $this->hasValidationOdgAdmin() || $this->hasValidationOdgRegion();
    }

    public function hasValidationOdgAdminOrAuto(){
      return $this->hasValidationOdgAdmin() || $this->hasValidationOdgAuto();
    }

    public function hasSpecificiteLot(){
      return isset($this->configuration['specificite_lot']) && boolval($this->configuration['specificite_lot']);
    }

    public function hasLogementAdresse() {
        return isset($this->configuration['logement_adresse']) && boolval($this->configuration['logement_adresse']);
    }

    public function getSpecificites(){
      if($this->hasSpecificiteLot()){
        return $this->configuration['specificites'];
      }
    }

    public function hasContenances() {
        return isset($this->configuration['contenances']) && boolval($this->configuration['contenances']);
    }

    public function getContenances(){
      if($this->hasContenances()){
        return $this->configuration['contenances'];
      }
    }
}

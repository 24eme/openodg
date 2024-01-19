<?php

class AdelpheConfiguration {

    private static $_instance = null;
    protected $configuration;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function load() {
        $this->configuration = sfConfig::get('adelphe_configuration_adelphe', []);
    }

    public function __construct() {
        if(!sfConfig::has('adelphe_configuration_adelphe')) {
    			throw new sfException("La configuration pour adelphe n'a pas été défini pour cette application");
    		}
        $this->load();
    }

    public function getTauxForfaitaireBib(){
      if (isset($this->configuration['taux_forfaitaire_bib']) && $this->configuration['taux_forfaitaire_bib']) {
        return $this->configuration['taux_forfaitaire_bib'];
      }
      return 0;
    }

}

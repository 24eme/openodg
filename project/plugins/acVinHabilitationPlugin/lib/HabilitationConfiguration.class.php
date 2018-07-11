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

    public function getActivites() {
        if(!isset($this->configuration['demande']['activites'])) {

            return array();
        }

        return $this->configuration['demande']['activites'];
    }

    public function getDemandeStatuts() {
        if(!isset($this->configuration['demande']['statuts'])) {

            return array();
        }

        return $this->configuration['demande']['statuts'];
    }

    public function getDemandeAutomatique() {
        if(!isset($this->configuration['demande']['automatique'])) {

            return array();
        }

        return $this->configuration['demande']['automatique'];
    }

    public function getDemandeHabilitations() {
        if(!isset($this->configuration['demande']['habilitations'])) {

            return array();
        }

        return $this->configuration['demande']['habilitations'];
    }

}

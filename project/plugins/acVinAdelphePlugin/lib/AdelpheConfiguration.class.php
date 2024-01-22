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

    public function getFonctionCalculSeuil() {
      if (isset($this->configuration['fonction_calcul_seuil']) && $this->configuration['fonction_calcul_seuil']) {
        return $this->configuration['fonction_calcul_seuil'];
      }
      return null;
    }

    public function getTauxForfaitaireBib() {
      if (isset($this->configuration['taux_forfaitaire_bib']) && $this->configuration['taux_forfaitaire_bib']) {
        return $this->configuration['taux_forfaitaire_bib'];
      }
      return 0;
    }

    public function getPrixUnitaireBib() {
        if (isset($this->configuration['prix_unitaire_bib']) && $this->configuration['prix_unitaire_bib']) {
            return $this->configuration['prix_unitaire_bib'];
        }
        return 0;
    }

    public function getPrixUnitaireBouteille() {
        if (isset($this->configuration['prix_unitaire_bouteille']) && $this->configuration['prix_unitaire_bouteille']) {
            return $this->configuration['prix_unitaire_bouteille'];
        }
        return 0;
    }

    public function getUrlAdelphe() {
        if (isset($this->configuration['url_adelphe']) && $this->configuration['url_adelphe']) {
            return $this->configuration['url_adelphe'];
        }
        return 0;
    }
}

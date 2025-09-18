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

    public function isModuleEnabled() {
        return in_array('habilitation', sfConfig::get('sf_enabled_modules'));
    }

    public function getActivites() {
        if(!isset($this->configuration['activites'])) {

            return array();
        }

        return $this->configuration['activites'];
    }

    public function isSuiviParDemande() {

        return count($this->getDemandeStatuts()) > 0;
    }

    public function isListingParDemande() {
        if(!isset($this->configuration['demande']['listing'])) {

            return $this->isSuiviParDemande();
        }

        return $this->configuration['demande']['listing'];
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

    public function getDemandeStatutsFerme() {

        return array('VALIDE', 'REFUSE', 'ANNULE');
    }

    public function getProduitAtHabilitationLevel($produit){

      if (!$produit) {
          return null;
      }
      $produithab = $produit->getAppellation();
      $hash = $produithab->getHash();
      $h = str_replace(['/MOU/','/EFF/', '/VDB/'], '/TRANQ/', $hash);
      if ($hash != $h) {
          $produithab = $produithab->getDocument()->get($h);
      }

      if(!isset($this->configuration['produits'])){
        return $produithab;
      }
      if(!isset($this->configuration['produits']['convert'])){
        return $produithab;
      }

      $key = $produithab->getKey();

      if(isset($this->configuration['produits']['convert'][$key])) {
        $converted_key = $this->configuration['produits']['convert'][$key];
        return $produithab->getParent()->get($converted_key);
      }

      return $produithab;

    }

}

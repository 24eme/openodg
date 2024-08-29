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

    public function hasShowFilterProduitsConfiguration() {
        if(!isset($this->configuration['show_filter_produits_configuration'])) {
            return true;
        }

        return $this->configuration['show_filter_produits_configuration'];
    }

    /*
     * Seules les parcelles ayant au moins une troisième feuille sont prises
     * en compte dans les synthèse
     */
    public function isJeunesVignesEnabled() {
        return true;
    }

    public function isJeunesVignes3emeFeuille() {
        if(!isset($this->configuration['troisieme_feuille'])) {
            return false;
        }
        return $this->configuration['troisieme_feuille'];
    }

    public function isAres()
    {
        return $this->configuration['unit'] == "ares";
    }

    public function getAiresInfos() {
        if(!isset($this->configuration['aires'])) {

            return array();
        }
        return $this->configuration['aires'];
    }

    public function getAireInfoFromDenominationId($id) {
        foreach($this->getAiresInfos() as $k => $aire) {
            if ($aire['denomination_id'] == $id) {
                return $aire;
            }
        }
        return null;
    }

    public function getAireInfo($key) {
        if(!isset($this->configuration['aires'][$key])) {

            return null;
        }

        return $this->configuration['aires'][$key];
    }

    public function affectationNeedsIntention() {
        if(!isset($this->configuration['affectation'])) {
            return true;
        }
        if(!isset($this->configuration['affectation']['needs_intention'])) {
            return true;
        }
        return $this->configuration['affectation']['needs_intention'];

    }

    public function isManquantMandatory() {
        if(!isset($this->configuration['manquant']) || !isset($this->configuration['manquant']['mandatory'])) {
            return false;
        }
        return $this->configuration['manquant']['mandatory'];
    }

    public function getManquantPCMin() {
        if(!isset($this->configuration['manquant']) || !isset($this->configuration['manquant']['pc_min'])) {
            return 20;
        }
        return $this->configuration['manquant']['pc_min'];
    }

    public function getEcartRangsMax() {
        if(!isset($this->configuration['ecart_rangs_max'])) {
            return null;
        }
        return $this->configuration['ecart_rangs_max'];
    }
    public function getEcartPiedsMin() {
        if(!isset($this->configuration['ecart_pieds_min'])) {
            return null;
        }
        return $this->configuration['ecart_pieds_min'];
    }
    public function getEcartPiedsMax() {
        if(!isset($this->configuration['ecart_pieds_max'])) {
            return null;
        }
        return $this->configuration['ecart_pieds_max'];
    }
    public function hasDeclarationsLiees() {
        return (isset($this->configuration['declarations_liees']))? $this->configuration['declarations_liees'] : false;
    }
    public function isParcellesFromAffectationparcellaire() {
        return (isset($this->configuration['parcelles_from_affectationparcellaire']))? $this->configuration['parcelles_from_affectationparcellaire'] : false;
    }
}

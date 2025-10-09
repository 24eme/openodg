<?php

class PotentielProduction {

    private $parcellaire = null;
    private $parcellaire_affectation = null;
    private $produits = [];

    private $table_potentiel = [];
    private $potentiel_de_production = [];
    private $encepagement = [];

    private static $parcellaires = [];
    private static $affectations = [];
    private static $potentiels = [];

    public static function retrievePotentielProductionFromParcellaire(Parcellaire $parcellaire, $date = null, $affectation_be_validated = true) {
        $client = ParcellaireAffectationClient::getInstance();
        if (method_exists($client, "findPreviousByIdentifiantAndDate")) {
            $affectation = ParcellaireAffectationClient::getInstance()->findPreviousByIdentifiantAndDate($parcellaire->identifiant, $date);
            return PotentielProduction::cacheCreatePotentielProduction($parcellaire, $affectation, $affectation_be_validated);
        }

        return null;
    }

    public static function retrievePotentielProductionFromIdentifiant($identifiant, $date = null, $affectation_be_validated = true) {
        $client = ParcellaireAffectationClient::getInstance();
        if (method_exists($client, "findPreviousByIdentifiantAndDate")) {
            $parcellaire = ParcellaireClient::getInstance()->findPreviousByIdentifiantAndDate($identifiant, $date);
            $affectation = ParcellaireAffectationClient::getInstance()->findPreviousByIdentifiantAndDate($identifiant, $date);
            return PotentielProduction::cacheCreatePotentielProduction($parcellaire, $affectation, $affectation_be_validated);
        }

        return null;
    }

    public static function cacheCreatePotentielProduction(Parcellaire $parcellaire, ParcellaireAffectation $affectation = null, $affectation_be_validated = true) {

        $parcellaire_cache_id = $parcellaire->_id.$parcellaire->_rev;
        self::$parcellaires[$parcellaire_cache_id] = $parcellaire;
        if ($affectation) {
            self::$affectations[$affectation->_id.$affectation->_rev] = $affectation;
        }
        $affectation_cache_id = ($affectation) ? $affectation->_id.$affectation->_rev : '';

        $cachekey = implode('-', [$parcellaire_cache_id, $affectation_cache_id, $affectation_be_validated]);

        if (!isset(self::$potentiels[$cachekey])) {
            self::$potentiels[$cachekey] = CacheFunction::cache('model', "PotentielProduction::createPotentielProduction", array($parcellaire_cache_id, $affectation_cache_id, $affectation_be_validated));
        }

        return self::$potentiels[$cachekey];
    }

    public static function createPotentielProduction($parcellaire_id, $affectation_id, $affectation_be_validated = true) {
        $parcellaire = self::$parcellaires[$parcellaire_id];
        $affectation = null;
        if ($affectation_id) {
            $affectation = self::$affectations[$affectation_id];
        }
        return new PotentielProduction($parcellaire, $affectation, $affectation_be_validated);
    }

    private function __construct(Parcellaire $parcellaire, ParcellaireAffectation $affectation = null, $affectation_be_validated = true) {
        $this->parcellaire = $parcellaire;
        if($affectation && (!$affectation_be_validated || $affectation->isValidee()))  {
            $this->parcellaire_affectation = $affectation;
        }

        foreach($this->getLibellesPotentielProduits() as $l => $p) {
            $ppproduit = null;
            $ppproduit = new PotentielProductionProduit($this, $l, $p);
            if ($ppproduit && $ppproduit->hasEncepagement()) {
                $this->produits[$l] = $ppproduit;
            }
        }
    }

    private function getLibellesPotentielProduits() {
        $libelles = [];
        $cepages = [];
        foreach($this->parcellaire->getParcelles() as $p) {
            $cepages[$p->cepage] = 1;
        }
        foreach(array_keys($cepages) as $cepage) {
            foreach($this->parcellaire->getCachedProduitsByCepageFromHabilitationOrConfiguration($cepage) as $prod) {
                $l = preg_replace('/ +$/', '', $prod->getLibelleFormat([], "%a% %m% %l% - %co% %ce%"));
                $libelles[$l] = $prod;
            }
            if (ParcellaireConfiguration::getInstance()->isJeunesVignesEnabled() && !$p->hasJeunesVignes()) {
                $libelles['XXXXjeunes vignes'] = null;
            }
        }
        ksort($libelles);
        return $libelles;
    }

    public function getProduits() {
        return array_values($this->produits);
    }

    public function getParcellaire() {
        return $this->parcellaire;
    }
    public function getParcellaireAffectation() {
        return $this->parcellaire_affectation;
    }

    public function hasPotentiels() {
        foreach ($this->produits as $key => $prod) {
            if ($prod->hasPotentiel()) {
                return true;
            }
        }
        return false;
    }

    public function getProduitsFromParcelleId($pid)
    {
        $hash_produits = [];
        foreach ($this->produits as $key => $prod) {
            if ($prod->hasPotentiel() && $prod->hasParcelleId($pid) && $prod->getHashProduitAffectation()) {
                $hash_produits[] = $prod->getHashProduitAffectation();
            }
        }
        return $hash_produits;
    }

    public function parcellaire2refIsAffectation()
    {
        foreach ($this->getProduits() as $produit) {
            if ($produit && $produit->hasPotentiel() && $produit->parcellaire2refIsAffectation()) {
                return true;
            }
        }
        return false;
    }
}

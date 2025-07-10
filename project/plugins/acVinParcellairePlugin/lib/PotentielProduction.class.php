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

    public static function retrievePotentielProductionFromParcellaire(Parcellaire $parcellaire, $date = null) {
        $client = ParcellaireAffectationClient::getInstance();
        if (method_exists($client, "findPreviousByIdentifiantAndDate")) {
            $affectation = ParcellaireAffectationClient::getInstance()->findPreviousByIdentifiantAndDate($parcellaire->identifiant, $date);
            return PotentielProduction::cacheCreatePotentielProduction($parcellaire, $affectation);
        }

        return null;
    }

    public static function retrievePotentielProductionFromIdentifiant($identifiant, $date = null) {
        $client = ParcellaireAffectationClient::getInstance();
        if (method_exists($client, "findPreviousByIdentifiantAndDate")) {
            $parcellaire = ParcellaireClient::getInstance()->findPreviousByIdentifiantAndDate($identifiant, $date);
            $affectation = ParcellaireAffectationClient::getInstance()->findPreviousByIdentifiantAndDate($identifiant, $date);
            return PotentielProduction::cacheCreatePotentielProduction($parcellaire, $affectation);
        }

        return null;
    }

    public static function cacheCreatePotentielProduction(Parcellaire $parcellaire, ParcellaireAffectation $affectation = null) {

        self::$parcellaires[$parcellaire->_id.$parcellaire->_rev] = $parcellaire;
        if ($affectation) {
            self::$affectations[$affectation->_id.$affectation->_rev] = $affectation;
        }
        return CacheFunction::cache('model', "PotentielProduction::createPotentielProduction", array($parcellaire->_id.$parcellaire->_rev, ($affectation) ? $affectation->_id.$affectation->_rev : null));
    }

    public static function createPotentielProduction($parcellaire_id, $affectation_id) {
        $parcellaire = self::$parcellaires[$parcellaire_id];
        $affectation = null;
        if ($affectation_id) {
            $affectation = self::$affectations[$affectation_id];
        }
        return new PotentielProduction($parcellaire, $affectation);
    }

    private function __construct(Parcellaire $parcellaire, ParcellaireAffectation $affectation = null) {
        $this->parcellaire = $parcellaire;
        $this->parcellaire_affectation = $affectation;

        foreach($this->getLibellesPotentielProduits() as $k) {
            if (!$k) {
                continue;
            }
            $ppproduit = null;
            $ppproduit = new PotentielProductionProduit($this, $k);
            if ($ppproduit && $ppproduit->hasEncepagement()) {
                $this->produits[$k] = $ppproduit;
            }
        }
    }

    private function getLibellesPotentielProduits() {
        $libelles = [];
        foreach($this->parcellaire->getParcelles() as $p) {
            $cepage = $p->getCepage();
            foreach($this->parcellaire->getCachedProduitsByCepageFromHabilitationOrConfiguration($cepage) as $prod) {
                $l = preg_replace('/ +$/', '', $prod->formatProduitLibelle("%a% %m% %l% - %co% %ce%"));
                $libelles[$l] = $l;
            }
            if (ParcellaireConfiguration::getInstance()->isJeunesVignesEnabled() && !$p->hasJeunesVignes()) {
                $libelles['XXXXjeunes vignes'] = 'XXXXjeunes vignes';
            }
        }
        ksort($libelles);
        return array_keys($libelles);
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

}

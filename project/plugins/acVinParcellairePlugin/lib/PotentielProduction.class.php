<?php

class PotentielProduction {

    private $parcellaire = null;
    private $parcellaire_affectation = null;
    private $produits = [];

    private $table_potentiel = [];
    private $potentiel_de_production = [];
    private $encepagement = [];

    public static function retrievePotentielProductionFromParcellaire(Parcellaire $parcellaire, $date = null) {
        $affectation = ParcellaireAffectationClient::getInstance()->findPreviousByIdentifiantAndDate($parcellaire->identifiant, $date);
        return new PotentielProduction($parcellaire, $affectation);
    }

    public static function retrievePotentielProductionFromIdentifiant($identifiant, $date = null) {
        $parcellaire = ParcellaireClient::getInstance()->findPreviousByIdentifiantAndDate($identifiant, $date);
        $affectation = ParcellaireAffectationClient::getInstance()->findPreviousByIdentifiantAndDate($identifiant, $date);
        return new PotentielProduction($parcellaire, $affectation);
    }

    public function __construct(Parcellaire $parcellaire, ParcellaireAffectation $affectation = null) {
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

}

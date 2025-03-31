<?php

class PotentielProduction {

    private $parcellaire = null;
    private $produits = [];

    private $table_potentiel = [];
    private $potentiel_de_production = [];
    private $encepagement = [];

    public function __construct(Parcellaire $parcellaire) {
        $this->parcellaire = $parcellaire;

        foreach (ParcellaireConfiguration::getInstance()->getPotentielGroupes() as $groupe_key) {
            $groupe_synthese = ParcellaireConfiguration::getInstance()->getGroupeSyntheseLibelle($groupe_key);
            $synthese = $this->parcellaire->getSyntheseProduitsCepages(ParcellaireConfiguration::getInstance()->getGroupeFilterProduitHash($groupe_key), ParcellaireConfiguration::getInstance()->getGroupeFilterINSEE($groupe_key));
            if (!count($synthese)) {
                continue;
            }
            if (!isset($synthese[$groupe_synthese])) {
                continue;
            }
            $ppproduit = new PotentielProductionProduit($this, $groupe_key, $groupe_synthese, $synthese);
            $this->produits[$groupe_key] = $ppproduit;
        }
    }

    public function getProduits() {
        return array_values($this->produits);
    }

}

<?php

class PotentielProductionProduit {

    private $key;
    private $libelle;
    private $rules = [];
    private $superficie_encepagement;
    private $superficie_max;
    private $cepages_superficie = [];

    private $encepagement;
    private $potentiel_production;

    public function __construct(PotentielProduction $p, $key, $libelle, $cepages) {
        $this->potentiel_production = $p;
        $this->key = $key;
        $this->libelle = $libelle;
        $this->cepages_superficie = $cepages;
    }

    public function addRule($r) {
        $this->rules[] = $r;
    }

    public function getLibelle() {
        return $this->libelle;
    }

    public function setSuperficieEncepagement($e) {
        $this->superficie_encepagement = $e;
    }
    public function setSuperficieMax($s) {
        $this->superficie_max = $s;
    }

    public function getSuperficieEncepagement() {
        return round($this->superficie_encepagement, 5);
    }

    public function getSuperficieMax() {
        return round(floatval($this->superficie_max), 5);
    }

    public function hasLimit() {
        return $this->getSuperficieEncepagement() != $this->getSuperficieMax();
    }

    public function getCepages() {
        return array_keys($this->cepages_superficie);
    }

    public function getRules() {
        return $this->rules;
    }
}

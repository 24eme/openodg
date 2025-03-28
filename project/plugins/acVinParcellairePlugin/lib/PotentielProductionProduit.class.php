<?php

class PotentielProductionProduit {

    private $key;
    private $libelle;
    private $rules = [];
    private $superficie_encepagement;
    private $superficie_max;
    private $cepages = [];

    private $encepagement;
    private $potentiel_production;

    public function __construct(PotentielProduction $p, $key, $libelle, $cepages) {
        $this->potentiel_production = $p;
        $this->key = $key;
        $this->libelle = $libelle;
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
        return $this->superficie_encepagement;
    }

    public function getSuperficieMax() {
        return floatval($this->superficie_max);
    }

    public function getRules() {
        return $this->rules;
    }
}

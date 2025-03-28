<?php

class PotentielProductionRule {

    private $cepages_superficie;
    private $impact;
    private $limit;
    private $res;
    private $sens;
    private $somme;
    private $name;
    private $potentiel_production_produit;

    public function __construct(PotentielProductionProduit $p, $rule_name, $potentiel_rule) {
        $this->potentiel_production_produit = $p;
        $this->name = $rule_name;
        $this->cepages_superficie = $potentiel_rule['cepages'];
        if (isset($potentiel_rule['impact'])) {
            $this->impact = $potentiel_rule['impact'];
        }else{
            $this->impact = 'ok';
        }
        $this->limit = $potentiel_rule['limit'];
        $this->res = $potentiel_rule['res'];
        $this->sens = $potentiel_rule['sens'];
        $this->somme = $potentiel_rule['somme'];
    }

    public function getCSSClass() {
        switch ($this->impact) {
            case 'blocker':
                return 'danger';
            case 'disabling':
                return 'info';
            case 'disabled':
                return '';
        }
        return 'warning';
    }

    public function isDisabling() {
        return ($this->impact == 'disabling');
    }

    public function getResult() {
        return $this->res;
    }
    public function getSomme() {
        return $this->somme;
    }
    public function getCepages() {
        return array_keys($this->cepages_superficie);
    }
    public function getImpact() {
        return $this->impact;
    }
    public function getLimit() {
        return $this->limit;
    }
    public function getSens() {
        return $this->sens;
    }
    public function getLibelle() {
        return $this->name;
    }

}

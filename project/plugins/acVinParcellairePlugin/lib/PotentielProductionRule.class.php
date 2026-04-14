<?php

class PotentielProductionRule {

    private $cepages_superficie;
    private $rule_type = null;
    private $regle = null;
    private $limit;
    private $res;
    private $sens;
    private $somme;
    private $name;
    private $simple_restriction = null;
    private $potentiel_production_produit;

    const TYPE_GOE = Simplex\Restriction::TYPE_GOE;
    const TYPE_LOE = Simplex\Restriction::TYPE_LOE;

    public function __construct(PotentielProductionProduit $p, $regle) {
        $this->potentiel_production_produit = $p;

        $this->regle = $regle;
        $this->res = false;
        $this->somme = 0;
        $this->limit = null;
        $this->cepages_superficie = $this->potentiel_production_produit->getCepagesFromCategorie($this->regle['category']);
        $encepagement = $this->potentiel_production_produit->getSuperficieEncepagement();

        $this->name = $this->regle['fonction'].'('.$this->regle['category'].') '.$this->regle['sens'].' '.$this->regle['limit'];

        if (($this->regle['sens'] != '>=') && ($this->regle['sens'] != '<=')) {
            throw new sfException('sens "'.$this->regle['sens'].'" non géré');
        }

        switch ($this->regle['fonction']) {
            case 'SAppliqueSiSomme':
                $this->somme = array_sum($this->cepages_superficie);
                $this->limit = $this->regle['limit'];
                if ($this->regle['sens'] == '>=') {
                    $this->res = ($this->somme >= $this->regle['limit']);
                }elseif ($this->regle['sens'] == '<=') {
                    $this->res = ($this->somme <= $this->regle['limit']);
                }
                $this->rule_type = 'disabling';
                break;
            case 'Nombre':
                $this->somme = count($this->cepages_superficie);
                $this->limit = $this->regle['limit'];
                if ($this->regle['sens'] == '>=') {
                    $this->res = ($this->somme >= $this->regle['limit']);
                }elseif ($this->regle['sens'] == '<=') {
                    $this->res = ($this->somme <= $this->regle['limit']);
                }
                $this->rule_type = 'blocker';
                break;
            case 'SAppliqueSiProportionSomme':
                $this->somme = array_sum($this->cepages_superficie);
                $this->limit = $encepagement * $this->regle['limit'];
                if ($this->regle['sens'] == '>=') {
                    $this->res = ($this->somme >= $this->limit);
                }elseif ($this->regle['sens'] == '<=') {
                    $this->res = ($this->somme <= $this->limit);
                }
                $this->rule_type = 'disabling';
                break;
            case 'ProportionSomme':
                $this->somme = array_sum($this->cepages_superficie);
                $this->limit = $encepagement * $this->regle['limit'];
                if ($this->regle['sens'] == '>=') {
                    $this->res = (round($this->somme - $this->limit, 5) >= 0);
                    if ($this->regle['limit'] == 1)  {
                        $ratio = $this->potentiel_production_produit->getCepagesFromCategorie('cepages_couleur');
                        foreach (array_keys($this->cepages_superficie) as $k) {
                            $ratio[$k] = 0;
                        }
                        $this->simple_restriction = self::getNewRectrition($ratio, self::TYPE_LOE, 0);
                    } else {
                        $this->simple_restriction = self::getNewRectrition(PotentielProductionRule::addemptycepage($this->cepages_superficie, $this->potentiel_production_produit->getCepagesFromCategorie('cepages_couleur'), $this->regle['limit'] * -1), self::TYPE_GOE, 0);
                    }
                }elseif ($this->regle['sens'] == '<=') {
                    $this->res = (round($this->somme - $this->limit, 5) <= 0);
                    $this->simple_restriction = self::getNewRectrition(PotentielProductionRule::addemptycepage($this->cepages_superficie, $this->potentiel_production_produit->getCepagesFromCategorie('cepages_couleur'), $this->regle['limit'] * -1), self::TYPE_LOE, 0);
                }
                break;
            case 'ProportionChaque':
                $this->somme = 0;
                $this->limit = $encepagement * $this->regle['limit'];
                $this->res = true;
                foreach(array_keys($this->potentiel_production_produit->getCepagesFromCategorie('cepages_principaux')) as $c) {
                    if (!isset($this->cepages_superficie[$c])) {
                        continue;
                    }
                    $this->somme .= $this->cepages_superficie[$c].'|';
                    if ($this->regle['sens'] == '>=') {
                        $this->res &= ($this->cepages_superficie[$c] >=  $this->limit);
                        $this->simple_restriction = self::getNewRectrition(PotentielProductionRule::addemptycepage([$c => $this->cepages_superficie[$c]], $this->potentiel_production_produit->getCepagesFromCategorie('cepages_couleur'), $this->regle['limit'] * -1), Simplex\Restriction::TYPE_GOE, 0);
                    }elseif ($this->regle['sens'] == '<=') {
                        $this->res &= ($this->cepages_superficie[$c] <=  $this->limit);
                        $this->simple_restriction = self::getNewRectrition(PotentielProductionRule::addemptycepage([$c => $this->cepages_superficie[$c]], $this->potentiel_production_produit->getCepagesFromCategorie('cepages_couleur'), $this->regle['limit'] * -1), Simplex\Restriction::TYPE_LOE, 0);
                    }
                }
                break;
            default:
                throw new sfException('Fonction de Potentiel de production "'.$this->regle['fonction'].'" non gérée');
        }
    }

    public function getSimplexRestriction() {
        return $this->simple_restriction;
    }

    public function getCSSClass() {
        switch ($this->getImpact()) {
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
        return ($this->getImpact() == 'disabling');
    }

    public function isDisablingRule() {
        return ($this->rule_type == 'disabling');
    }
    public function isBlockingRule() {
        return ($this->rule_type == 'blocker');
    }

    public function getImpact() {
        if (!$this->res && $this->rule_type) {
            return $this->rule_type;
        }
        return null;
    }

    public function getResult() {
        return $this->res;
    }
    public function getSomme() {
        return $this->somme;
    }
    public function getPC() {
        if (!$this->getSomme()) {
            return ;
        }
        return $this->somme / ($this->limit / $this->getLimitPC());
    }
    public function getCepages() {
        return array_keys($this->cepages_superficie);
    }
    public function getLimit() {
        return $this->limit;
    }
    public function getLimitPC() {
        return $this->regle['limit'];
    }
    public function getSens() {
        return $this->regle['sens'];
    }
    public function getLibelle() {
        return $this->name;
    }

    public function getRegleFonction() {
        return $this->regle['fonction'];
    }

    public static function addemptycepage($original, $keys, $value = 0) {
        foreach(array_keys($keys) as $k) {
            if (!isset($original[$k])) {
                $original[$k] = $keys[$k] * $value;
            }else{
                $original[$k] += $keys[$k] * $value;
            }
        }
        ksort($original);
        return $original;
    }

    public static function getNewRectrition($ratio, $sens, $limit) {
        if (isset($_GET['verbose'])) {
            echo "<pre>";
            print_r(['new restriction', "ratio" => $ratio, "sens" => ($sens == self::TYPE_LOE) ? 'LOE' : 'GOE', 'limit' => $limit]);
            echo "</pre>";
        }
        return new Simplex\Restriction($ratio, $sens, $limit);
    }

    public static function createTask($default_ratio) {
        if (isset($_GET['verbose'])) {
            echo "<pre>";
            print_r(['new task', 'ratio' => $default_ratio]);
            echo "</pre>";
        }
        return new Simplex\Task(new Simplex\Func($default_ratio));
    }

}

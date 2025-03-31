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

    public function __construct(PotentielProduction $p, $key, $libelle, $synthese) {
        $this->potentiel_production = $p;
        $this->key = $key;
        $this->libelle = $libelle;

        $potentiel_has_desactive = false;
        $potentiel_sans_blocant = true;

        $this->cepages_par_categories = [];
        foreach (ParcellaireConfiguration::getInstance()->getGroupeCategories($this->key) as $category_key => $category_cepages) {
            $this->cepages_par_categories[$category_key] = [];
            foreach($category_cepages as $c) {
                if (isset($synthese[$libelle]) && isset($synthese[$libelle]['Cepage'][$c])) {
                    $this->cepages_par_categories[$category_key][$c] = $synthese[$libelle]['Cepage'][$c]['superficie_max'];
                }
            }
        }
        $this->cepages_par_categories['cepages_couleur'] = [];
        $this->cepages_par_categories['cepages_toutes_couleurs'] = [];

        $encepagement = 0;
        foreach($synthese as $synthese_libelle => $synthese_couleur) {
            foreach($synthese_couleur as $cepages) {
                foreach($cepages as $k => $superficies) {
                    if ($k == 'Total') {
                        continue;
                    }
                    if (strpos($k, 'XXX') !== false) {
                        continue;
                    }
                    if (!isset($superficies['superficie_max'])) {
                        continue;
                    }
                    if (!isset($this->cepages_par_categories['cepages_toutes_couleurs'][$k])) {
                        $this->cepages_par_categories['cepages_toutes_couleurs'][$k] = $superficies['superficie_max'];
                    }
                    if ($synthese_libelle != $libelle) {
                        continue;
                    }
                    $this->cepages_par_categories['cepages_couleur'][$k] = $superficies['superficie_max'];
                    $encepagement += $superficies['superficie_max'];
                }
            }
        }

        $this->cepages_superficie = $this->cepages_par_categories['cepages_couleur'];

        $task = new Simplex\Task(new Simplex\Func(PotentielProductionRule::addemptycepage($this->cepages_par_categories['cepages_couleur'], $this->cepages_par_categories['cepages_couleur'])));

        foreach(ParcellaireConfiguration::getInstance()->getGroupeRegles($this->key) as $regle) {
            $pprule = new PotentielProductionRule($this, $regle);
            $this->addRule($pprule);
            $simplex = $pprule->getSimplexRestriction();
            if ($simplex) {
                $task->addRestriction($simplex);
            }
            if ($pprule->isDisablingRule()) {
                $potentiel_has_desactive = $potentiel_has_desactive || !$pprule->getResult();
            }
            if ($pprule->isBlockingRule()) {
                $potentiel_sans_blocant = $potentiel_sans_blocant && $pprule->getResult();
            }
        }
        foreach(array_keys($this->cepages_superficie) as $c) {
            if ($this->cepages_superficie[$c]) {
                $task->addRestriction(new Simplex\Restriction(PotentielProductionRule::addemptycepage([$c => $this->cepages_superficie[$c]], $this->cepages_superficie), Simplex\Restriction::TYPE_LOE, $this->cepages_superficie[$c]));
            }
        }

        $solver = new Simplex\Solver($task);
        $solution = $solver->getSolution();
        if ($solution) {
            $optimum = $solver->getSolutionValue($solution);
            $this->superficie_max = round($optimum->toFloat(), 5);
        } else {
            print_r(['pas doptimum']);
            $this->superficie_max = "IMPOSSIBLE";
        }
        if (!$potentiel_sans_blocant) {
            print_r(['blocant']);
            $this->superficie_max = "IMPOSSIBLE";
        }
        if ($potentiel_has_desactive) {
            $this->superficie_max = round(array_sum($this->cepages_superficie), 5);
            $encepagement = array_sum($this->cepages_superficie);
        }
        $this->superficie_encepagement = round($encepagement, 5);

    }

    public function getCepagesFromCategorie($cat) {
        if (!isset($this->cepages_par_categories[$cat])) {
            return [];
        }
        return $this->cepages_par_categories[$cat];
    }

    public function addRule($r) {
        $this->rules[] = $r;
    }

    public function getLibelle() {
        return $this->libelle;
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

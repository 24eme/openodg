<?php

class PotentielProductionProduit {

    private $key;
    private $libelle;
    private $rules = [];
    private $superficie_encepagement;
    private $superficie_max = null;
    private $cepages_superficie = [];

    private $potentiel_production;
    private $synthese = null;

    public function __construct(PotentielProduction $p, $libelle) {
        $this->potentiel_production = $p;
        $this->libelle = $libelle;
        foreach (ParcellaireConfiguration::getInstance()->getPotentielGroupes() as $groupe_key) {
            $produit_libelle = ParcellaireConfiguration::getInstance()->getGroupeSyntheseLibelle($groupe_key);
            if ($produit_libelle != $libelle) {
                continue;
            }
            $this->key = $groupe_key;
            break;
        }
        $this->initSynthese();
        $this->initEncepagement();
        if (strpos($this->libelle, 'XXX') === false) {
            $this->initPotentiel();
        }else {
            $this->libelle = str_replace('XXXXjeunes vignes', 'Jeunes vignes', $this->libelle);
        }
    }

    public function initEncepagement() {
        $this->cepages_par_categories = [];
        if ($this->key) {
            foreach (ParcellaireConfiguration::getInstance()->getGroupeCategories($this->key) as $category_key => $category_cepages) {
                $this->cepages_par_categories[$category_key] = [];
                foreach($category_cepages as $c) {
                    if (isset($this->synthese[$this->libelle]) && isset($this->synthese[$this->libelle]['Cepage'][$c])) {
                        $this->cepages_par_categories[$category_key][$c] = $this->synthese[$this->libelle]['Cepage'][$c]['superficie_max'];
                    }
                }
            }
        }
        $this->cepages_par_categories['cepages_couleur'] = [];
        $this->cepages_par_categories['cepages_toutes_couleurs'] = [];
        $this->superficie_encepagement = 0;
        foreach($this->synthese as $this->synthese_libelle => $this->synthese_couleur) {
            foreach($this->synthese_couleur as $cepages) {
                foreach($cepages as $k => $superficies) {
                    if ($k == 'Total') {
                        continue;
                    }
                    if (strpos($k, $this->libelle) === false && strpos($k, 'XXX') !== false) {
                        continue;
                    }
                    if (!isset($superficies['superficie_max'])) {
                        continue;
                    }
                    if (!isset($this->cepages_par_categories['cepages_toutes_couleurs'][$k])) {
                        $this->cepages_par_categories['cepages_toutes_couleurs'][$k] = $superficies['superficie_max'];
                    }
                    if ($this->synthese_libelle != $this->libelle) {
                        continue;
                    }
                    $this->cepages_par_categories['cepages_couleur'][$k] = $superficies['superficie_max'];
                    $this->superficie_encepagement += $superficies['superficie_max'];
                }
            }
        }
        $this->cepages_superficie = $this->cepages_par_categories['cepages_couleur'];
    }

    public function initPotentiel() {

        if (!$this->key) {
            return;
        }
        if (!$this->superficie_encepagement) {
            return;
        }

        $potentiel_has_desactive = false;
        $potentiel_sans_blocant = true;

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
            $this->superficie_max = "IMPOSSIBLE";
        }
        if (!$potentiel_sans_blocant) {
            $this->superficie_max = "IMPOSSIBLE";
        }
        if ($potentiel_has_desactive) {
            $this->superficie_max = round(array_sum($this->cepages_superficie), 5);
            $this->superficie_encepagement = round(array_sum($this->cepages_superficie), 5);
        }

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

    public function hasEncepagement() {
        return ($this->superficie_encepagement);
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

    public function hasSuperificieMax() {
        return $this->superficie_max !== null;
    }

    public function hasPotentiel() {
        return ($this->key);
    }

    public function parcellaire2refIsAffectation() {
        return (ParcellaireConfiguration::getInstance()->affectationIsParcellaire2Reference($this->key) && $this->potentiel_production->getParcellaireAffectation());
    }

    public function getParcellaire2Ref() {
        if (ParcellaireConfiguration::getInstance()->affectationIsParcellaire2Reference($this->key)) {
            return $this->potentiel_production->getParcellaireAffectation();
        }
        return $parcellaire2ref = $this->potentiel_production->getParcellaire();
    }

    private function initSynthese() {
        $filter_produit_hash = ParcellaireConfiguration::getInstance()->getGroupeFilterProduitHash($this->key);
        $filter_insee = ParcellaireConfiguration::getInstance()->getGroupeFilterINSEE($this->key);

        $this->synthese = array();
        $parcellaire2ref = $this->getParcellaire2Ref();
        if (!$parcellaire2ref) {
            $this->synthese = [];
            return [];
        }
        foreach($parcellaire2ref->getParcelles() as $p) {
            if ($filter_produit_hash === true && !$p->produit_hash) {
                continue;
            }
            if ($filter_produit_hash && is_string($filter_produit_hash) && strpos($p->produit_hash, $filter_produit_hash) === false) {
                continue;
            }
            if (($parcellaire2ref->type == ParcellaireAffectationClient::TYPE_MODEL) && !$p->affectee)  {
                continue;
            }
            if ($filter_insee && !in_array($p->code_commune, $filter_insee)) {
                continue;
            }
            $cepage = $p->getCepage();
            $libelles = array();
            foreach($this->potentiel_production->getParcellaire()->getCachedProduitsByCepageFromHabilitationOrConfiguration($cepage) as $prod) {
                $libelles[] = preg_replace('/ +$/', '', $prod->formatProduitLibelle("%a% %m% %l% - %co% %ce%"));
            }
            if (!count($libelles)) {
                $libelles[] = '';
            }
            if (ParcellaireConfiguration::getInstance()->isJeunesVignesEnabled() && !$p->hasJeunesVignes()) {
                $libelles[] = 'XXXXjeunes vignes';
                $cepage .= ' - XXXXjeunes vignes';
            }
            sort($libelles);
            foreach($libelles as $libelle) {
                if (!isset($this->synthese[$libelle])) {
                    $this->synthese[$libelle] = array();
                    $this->synthese[$libelle]['Total'] = array();
                    $this->synthese[$libelle]['Total']['Total'] = array();
                    $this->synthese[$libelle]['Total']['Total']['superficie_max'] = 0;
                }
                if (!isset($this->synthese[$libelle]['Cepage'])) {
                    $this->synthese[$libelle]['Cepage'] = array();
                }
                if (!isset($this->synthese[$libelle]['Cepage'][$cepage])) {
                    $this->synthese[$libelle]['Cepage'][$cepage] = array();
                    $this->synthese[$libelle]['Cepage'][$cepage]['superficie_max'] = 0;
                }
                $this->synthese[$libelle]['Cepage'][$cepage]['superficie_max'] += $p->superficie;
                if (strpos($cepage, '- jeunes vignes') === false) {
                    $this->synthese[$libelle]['Total']['Total']['superficie_max'] += $p->superficie;
                }
            }
        }
        ksort($this->synthese);

        foreach ($this->synthese as $libelle => &$cepagetotal) {
            ksort($cepagetotal);
            foreach($cepagetotal as $l => &$cepages) {
                ksort($cepages);
            }
            if (count($cepagetotal['Cepage']) < 2) {
                unset($cepagetotal['Total']);
            }
        }
        return $this->synthese;

    }


}

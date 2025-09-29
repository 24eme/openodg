<?php

class PotentielProductionProduit {

    private $key;
    private $produit;
    private $libelle;
    private $rules = [];
    private $superficie_encepagement;
    private $superficie_max = null;
    private $cepages_superficie = [];

    private $potentiel_production;
    private $synthese = null;

    public function __construct(PotentielProduction $p, $parcellaire_produit_libelle, $produit_configuration) {
        $this->potentiel_production = $p;
        $this->libelle = $parcellaire_produit_libelle;
        $this->produit = $produit_configuration;
        $this->key = ParcellaireConfiguration::getInstance()->getGroupeKeyByProduitConf($produit_configuration);

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
        $this->parcelles_par_categories = [];
        if ($this->key) {
            foreach (ParcellaireConfiguration::getInstance()->getGroupeCategories($this->key) as $category_key => $category_cepages) {
                $this->cepages_par_categories[$category_key] = [];
                $this->parcelles_par_categories[$category_key] = [];
                foreach($category_cepages as $c) {
                    if (isset($this->synthese[$this->libelle]) && isset($this->synthese[$this->libelle]['Cepage'][$c])) {
                        $this->cepages_par_categories[$category_key][$c] = $this->synthese[$this->libelle]['Cepage'][$c]['superficie_max'];
                        $this->parcelles_par_categories[$category_key] = array_merge($this->parcelles_par_categories[$category_key], $this->synthese[$this->libelle]['Cepage'][$c]['parcelles_id']);
                    }
                }
            }
        }
        $this->cepages_par_categories['cepages_couleur'] = [];
        $this->cepages_par_categories['cepages_toutes_couleurs'] = [];
        $this->superficie_encepagement = 0;
        foreach($this->synthese as $synthese_libelle => $synthese_couleur) {
            foreach($synthese_couleur as $cepages) {
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
                    if ($synthese_libelle != $this->libelle) {
                        continue;
                    }
                    $this->cepages_par_categories['cepages_couleur'][$k] = $superficies['superficie_max'];
                    if (isset($this->cepages_par_categories['cepages_couleur'][$k])) {
                        $this->superficie_encepagement += $superficies['superficie_max'];
                    }
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

        $task = PotentielProductionRule::createTask(PotentielProductionRule::addemptycepage($this->cepages_par_categories['cepages_couleur'], $this->cepages_par_categories['cepages_couleur']));

        $is_all_ok = true;
        if (isset($_GET['verbose'])) {
            echo "<pre>";
            echo "règle ".$this->key.":\n";
            echo "=============================\n";
            echo "</pre>";
        }
        foreach(ParcellaireConfiguration::getInstance()->getGroupeRegles($this->key) as $regle) {
            if (isset($_GET['verbose'])) {
                echo "<pre>";
                print_r($regle);
                echo "</pre>";
            }
            $pprule = new PotentielProductionRule($this, $regle);
            $this->addRule($pprule);
            $simplex = $pprule->getSimplexRestriction();
            if ($simplex) {
                $is_all_ok = $is_all_ok && $pprule->getResult();
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
                $task->addRestriction(PotentielProductionRule::getNewRectrition(PotentielProductionRule::addemptycepage([$c => $this->cepages_superficie[$c]], $this->cepages_superficie), PotentielProductionRule::TYPE_LOE, $this->cepages_superficie[$c]));
            }
        }

        if ($is_all_ok) {
            $this->superficie_max = $this->superficie_encepagement;
        }else {
            $solver = new Simplex\Solver($task);
            $solution = $solver->getSolution();
            if ($solution) {
                $optimum = $solver->getSolutionValue($solution);
                $this->superficie_max = round($optimum->toFloat(), 5);
                if (isset($_GET['verbose'])) {
                    echo "<pre>";
                    echo "solution optimum: ".$this->superficie_max."\n";
                    echo "</pre>";
                }
            } else {
                $printer = new Simplex\Printer;
                if (isset($_GET['verbose'])) {
                        echo "<pre>";
                        echo "Impossible : pas de solution\n";
                        echo "solution:\n";
                        $printer->printSolution($solver);
                        echo "solver:\n";
                        $printer->printSolver($solver);
                        echo "</pre>";
                }
                $this->superficie_max = "IMPOSSIBLE";
            }
        }
        if (!$potentiel_sans_blocant) {
            $this->superficie_max = "IMPOSSIBLE";
            if (isset($_GET['verbose'])) {
                echo "<pre>";
                echo "Impossible : car potentiel blocant\n";
                echo "</pre>";
            }
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
        if (ParcellaireConfiguration::getInstance()->affectationIsParcellaire2Reference($this->key) && $this->potentiel_production->getParcellaireAffectation()) {
            return $this->potentiel_production->getParcellaireAffectation();
        }
        return $parcellaire2ref = $this->potentiel_production->getParcellaire();
    }

    private function initSynthese() {
        $filter_produit_hash = ParcellaireConfiguration::getInstance()->getGroupeFilterParcellaireProduitHash($this->key);
        $filter_insee = ParcellaireConfiguration::getInstance()->getGroupeFilterINSEE($this->key);
        $parcellaire2ref = $this->getParcellaire2Ref();
        if ($parcellaire2ref->type == ParcellaireAffectationClient::TYPE_MODEL && ParcellaireConfiguration::getInstance()->getHashProduitAffectation($this->key)) {
            $filter_produit_hash = ParcellaireConfiguration::getInstance()->getHashProduitAffectation($this->key);
        }
        $this->synthese = self::cacheSynthese($parcellaire2ref, $filter_produit_hash, $filter_insee);
    }

    private static $cache_init = [];
    private static function cacheSynthese($parcellaire2ref, $filter_produit_hash, $filter_insee) {
        $cache_id = sprintf('%s@%s@%s', $parcellaire2ref->_id, strval($filter_produit_hash), ($filter_insee) ? implode(',',$filter_insee) : '');
        if (!isset(self::$cache_init[$cache_id])) {
            self::$cache_init[$cache_id] = self::realSynthese($parcellaire2ref, $filter_produit_hash, $filter_insee);
        }
        return self::$cache_init[$cache_id];
    }

    private static function realSynthese($parcellaire2ref, $filter_produit_hash, $filter_insee) {
        $synthese = array();
        if (!$parcellaire2ref) {
            $synthese = [];
            return [];
        }
        if ($parcellaire2ref->type == ParcellaireClient::TYPE_MODEL) {
            $real_parcellaire = $parcellaire2ref;
        }else{
            $real_parcellaire = $parcellaire2ref->getParcellaire();
        }
        $synthese_cepage = [];
        foreach($parcellaire2ref->getParcelles($filter_produit_hash) as $p) {
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
            if (ParcellaireConfiguration::getInstance()->isJeunesVignesEnabled() && !$p->hasJeunesVignes()) {
                $cepage .= ' - XXXXjeunes vignes';
            }

            if (!isset($synthese_cepage[$cepage])) {
                $synthese_cepage[$cepage] = ['superficie_max' => 0, 'parcelles_id' => []];
            }
            $synthese_cepage[$cepage]['superficie_max'] += $p->superficie;
            $synthese_cepage[$cepage]['parcelles_id'][] = $p->getParcelleId();
        }

        foreach($synthese_cepage as $cepage => $cepages_data) {
            $libelles = array();
            $prods = $real_parcellaire->getCachedProduitsByCepageFromHabilitationOrConfiguration($cepage);
            foreach($prods as $prod) {
                $libelles[] = preg_replace('/ +$/', '', $prod->getLibelleFormat([], "%a% %m% %l% - %co% %ce%"));
            }
            if (!count($libelles)) {
                $libelles[] = '';
            }
            if (strpos($cepage, ' - XXXXjeunes vignes') !== false) {
                $libelles[] = 'XXXXjeunes vignes';
            }
            foreach($libelles as $libelle) {
                if (!isset($synthese[$libelle])) {
                    $synthese[$libelle] = array();
                    $synthese[$libelle]['Total'] = array();
                    $synthese[$libelle]['Total']['Total'] = array();
                    $synthese[$libelle]['Total']['Total']['superficie_max'] = 0;
                }
                if (!isset($synthese[$libelle]['Cepage'])) {
                    $synthese[$libelle]['Cepage'] = array();
                }
                if (!isset($synthese[$libelle]['Cepage'][$cepage])) {
                    $synthese[$libelle]['Cepage'][$cepage] = array();
                    $synthese[$libelle]['Cepage'][$cepage]['superficie_max'] = 0;
                    $synthese[$libelle]['Cepage'][$cepage]['parcelles_id'] = [];
                }
                $synthese[$libelle]['Cepage'][$cepage]['superficie_max'] += $cepages_data['superficie_max'];
                if (strpos($cepage, '- jeunes vignes') === false) {
                    $synthese[$libelle]['Total']['Total']['superficie_max'] += $cepages_data['superficie_max'];
                    $synthese[$libelle]['Cepage'][$cepage]['parcelles_id'] = array_merge($synthese[$libelle]['Cepage'][$cepage]['parcelles_id'], $cepages_data['parcelles_id']);
                }
            }
        }
        ksort($synthese);

        foreach ($synthese as $libelle => &$cepagetotal) {
            ksort($cepagetotal);
            foreach($cepagetotal as $l => &$cepages) {
                ksort($cepages);
            }
            if (count($cepagetotal['Cepage']) < 2) {
                unset($cepagetotal['Total']);
            }
        }
        return $synthese;

    }

    private $cache_parcelles = null;
    private function getParcelleIds() {
        if (!$this->cache_parcelles) {
            $this->cache_parcelles = [];
            foreach($this->parcelles_par_categories as $libelle => $parcelles) {
                $this->cache_parcelles = array_merge($this->cache_parcelles, $parcelles);
            }
            $this->cache_parcelles = array_unique($this->cache_parcelles);
        }
        return $this->cache_parcelles;
    }

    public function hasParcelleId($pid) {
        return in_array($pid, $this->getParcelleIds());
    }

    public function getHashProduitAffectation() {
        if (!$this->key) {
            return null;
        }
        return ParcellaireConfiguration::getInstance()->getHashProduitAffectation($this->key);
    }

    public function getProduitHash() {
        if(!$this->produit) {
            return null;
        }
        return $this->produit->getHash();
    }

}

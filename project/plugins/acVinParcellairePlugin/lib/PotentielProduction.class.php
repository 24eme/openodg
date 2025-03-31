<?php

class PotentielProduction {

    private $parcellaire = null;
    private $produits = [];

    private $table_potentiel = [];
    private $potentiel_de_production = [];
    private $encepagement = [];

    public function __construct(Parcellaire $parcellaire) {
        $this->parcellaire = $parcellaire;

        $categories = [];

        foreach (ParcellaireConfiguration::getInstance()->getPotentielGroupes() as $groupe_key) {
            $potentiel_has_desactive = false;
            $potentiel_sans_blocant = true;
            $groupe_synthese = ParcellaireConfiguration::getInstance()->getGroupeSyntheseLibelle($groupe_key);
            $synthese = $this->parcellaire->getSyntheseProduitsCepages(ParcellaireConfiguration::getInstance()->getGroupeFilterProduitHash($groupe_key), ParcellaireConfiguration::getInstance()->getGroupeFilterINSEE($groupe_key));
            if (!count($synthese)) {
                continue;
            }
            if (!isset($synthese[$groupe_synthese])) {
                continue;
            }
            foreach (ParcellaireConfiguration::getInstance()->getGroupeCategories($groupe_key) as $category_key => $category_cepages) {
                $categories[$category_key] = [];
                foreach($category_cepages as $c) {
                    if (isset($synthese[$groupe_synthese]) && isset($synthese[$groupe_synthese]['Cepage'][$c])) {
                        $categories[$category_key][$c] = $synthese[$groupe_synthese]['Cepage'][$c]['superficie_max'];
                    }
                }
            }
            $categories['cepages_couleur'] = [];
            $categories['cepages_toutes_couleurs'] = [];
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
                        if (!isset($categories['cepages_toutes_couleurs'][$k])) {
                            $categories['cepages_toutes_couleurs'][$k] = $superficies['superficie_max'];
                        }
                        if ($synthese_libelle != $groupe_synthese) {
                            continue;
                        }
                        $categories['cepages_couleur'][$k] = $superficies['superficie_max'];
                        $encepagement += $superficies['superficie_max'];
                    }
                }
            }

            $task = new Simplex\Task(new Simplex\Func(PotentielProductionRule::addemptycepage($categories['cepages_couleur'], $categories['cepages_couleur'])));

            $this->table_potentiel[$groupe_synthese] = [];

            $ppproduit = new PotentielProductionProduit($this, $groupe_key, $groupe_synthese, $categories[$category_key], $categories['cepages_couleur']);
            $this->produits[$groupe_key] = $ppproduit;

            foreach(ParcellaireConfiguration::getInstance()->getGroupeRegles($groupe_key) as $regle) {
                $pprule = new PotentielProductionRule($ppproduit, $regle, $categories);
                $ppproduit->addRule($pprule);
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
            foreach(array_keys($categories['cepages_couleur']) as $c) {
                if ($categories['cepages_couleur'][$c]) {
                    $task->addRestriction(new Simplex\Restriction(PotentielProductionRule::addemptycepage([$c => $categories['cepages_couleur'][$c]], $categories['cepages_couleur']), Simplex\Restriction::TYPE_LOE, $categories['cepages_couleur'][$c]));
                }
            }

            $solver = new Simplex\Solver($task);
            $solution = $solver->getSolution();
            if ($solution) {
                $optimum = $solver->getSolutionValue($solution);
                $ppproduit->setSuperficieMax(round($optimum->toFloat(), 5));
            } else {
                $ppproduit->setSuperficieMax("IMPOSSIBLE");
            }
            if (!$potentiel_sans_blocant) {
                $ppproduit->setSuperficieMax("IMPOSSIBLE");
            }
            if ($potentiel_has_desactive) {
                $ppproduit->setSuperficieMax(round(array_sum($categories['cepages_couleur']), 5));
                $encepagement = array_sum($categories['cepages_couleur']);
            }
            $ppproduit->setSuperficieEncepagement(round($encepagement, 5));
        }
    }

    public function getProduits() {
        return array_values($this->produits);
    }

}

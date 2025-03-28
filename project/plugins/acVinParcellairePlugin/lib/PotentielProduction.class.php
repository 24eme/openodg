<?php

class PotentielProduction {

    private $parcellaire = null;
    private $produits = [];

    public $table_potentiel = [];
    public $potentiel_de_production = [];
    public $encepagement = [];

    public function __construct(Parcellaire $parcellaire) {
        $this->parcellaire = $parcellaire;

        $categories = [];

        foreach (ParcellaireConfiguration::getInstance()->getPotentielGroupes() as $groupe_key) {
            $potentiel_has_desactive = true;
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

            $task = new Simplex\Task(new Simplex\Func($this->addemptycepage($categories['cepages_couleur'],$categories['cepages_couleur'])));

            $this->table_potentiel[$groupe_synthese] = [];

            $ppproduit = new PotentielProductionProduit($this, $groupe_key, $groupe_synthese, $categories['cepages_couleur']);
            $this->produits[$groupe_key] = $ppproduit;

            foreach(ParcellaireConfiguration::getInstance()->getGroupeRegles($groupe_key) as $regle) {
                $regle_nom = $regle['fonction'].'('.$regle['category'].') '.$regle['sens'].' '.$regle['limit'];
                $this->table_potentiel[$groupe_synthese][$regle_nom] = [];

                if (($regle['sens'] != '>=') && ($regle['sens'] != '<=')) {
                    throw new sfException('sens '.$regle['sens'].' non géré');
                }
                $this->table_potentiel[$groupe_synthese][$regle_nom]['sens'] = $regle['sens'];

                $this->table_potentiel[$groupe_synthese][$regle_nom]['cepages'] = $categories[$regle['category']];

                switch ($regle['fonction']) {
                    case 'SAppliqueSiSomme':
                        $this->table_potentiel[$groupe_synthese][$regle_nom]['somme'] = array_sum($categories[$regle['category']]);
                        $this->table_potentiel[$groupe_synthese][$regle_nom]['limit'] = $regle['limit'];
                        if ($regle['sens'] == '>=') {
                            $this->table_potentiel[$groupe_synthese][$regle_nom]['res'] = ($this->table_potentiel[$groupe_synthese][$regle_nom]['somme'] >= $this->table_potentiel[$groupe_synthese][$regle_nom]['limit']);
                        }elseif ($regle['sens'] == '<=') {
                            $this->table_potentiel[$groupe_synthese][$regle_nom]['res'] = ($this->table_potentiel[$groupe_synthese][$regle_nom]['somme'] <= $this->table_potentiel[$groupe_synthese][$regle_nom]['limit']);
                        }
                        $potentiel_has_desactive = $potentiel_has_desactive && !$this->table_potentiel[$groupe_synthese][$regle_nom]['res'];
                        if (!$this->table_potentiel[$groupe_synthese][$regle_nom]['res']) {
                            $this->table_potentiel[$groupe_synthese][$regle_nom]['impact'] = 'disabling';
                        }
                        break;
                    case 'Nombre':
                        $this->table_potentiel[$groupe_synthese][$regle_nom]['somme'] = count($categories[$regle['category']]);
                        $this->table_potentiel[$groupe_synthese][$regle_nom]['limit'] = $regle['limit'];
                        if ($regle['sens'] == '>=') {
                            $this->table_potentiel[$groupe_synthese][$regle_nom]['res'] = ($this->table_potentiel[$groupe_synthese][$regle_nom]['somme'] >= $this->table_potentiel[$groupe_synthese][$regle_nom]['limit']);
                        }elseif ($regle['sens'] == '<=') {
                            $this->table_potentiel[$groupe_synthese][$regle_nom]['res'] = ($this->table_potentiel[$groupe_synthese][$regle_nom]['somme'] <= $this->table_potentiel[$groupe_synthese][$regle_nom]['limit']);
                        }
                        $potentiel_sans_blocant = $potentiel_sans_blocant && $this->table_potentiel[$groupe_synthese][$regle_nom]['res'];
                        if (!$this->table_potentiel[$groupe_synthese][$regle_nom]['res']) {
                            $this->table_potentiel[$groupe_synthese][$regle_nom]['impact'] = 'blocker';
                        }
                        break;
                    case 'SAppliqueSiProportionSomme':
                        $this->table_potentiel[$groupe_synthese][$regle_nom]['somme'] = array_sum($categories[$regle['category']]);
                        $this->table_potentiel[$groupe_synthese][$regle_nom]['limit'] = $encepagement * $regle['limit'];
                        if ($regle['sens'] == '>=') {
                            $this->table_potentiel[$groupe_synthese][$regle_nom]['res'] = ($this->table_potentiel[$groupe_synthese][$regle_nom]['somme'] >= $this->table_potentiel[$groupe_synthese][$regle_nom]['limit']);
                        }elseif ($regle['sens'] == '<=') {
                            $this->table_potentiel[$groupe_synthese][$regle_nom]['res'] = ($this->table_potentiel[$groupe_synthese][$regle_nom]['somme'] <= $this->table_potentiel[$groupe_synthese][$regle_nom]['limit']);
                        }
                        $potentiel_has_desactive = $potentiel_has_desactive && !$this->table_potentiel[$groupe_synthese][$regle_nom]['res'];
                        if (!$this->table_potentiel[$groupe_synthese][$regle_nom]['res']) {
                            $this->table_potentiel[$groupe_synthese][$regle_nom]['impact'] = 'disabling';
                        }
                        break;
                    case 'ProportionSomme':
                        $this->table_potentiel[$groupe_synthese][$regle_nom]['somme'] = array_sum($categories[$regle['category']]);
                        $this->table_potentiel[$groupe_synthese][$regle_nom]['limit'] = $encepagement * $regle['limit'];
                        if ($regle['sens'] == '>=') {
                            $this->table_potentiel[$groupe_synthese][$regle_nom]['res'] = ($this->table_potentiel[$groupe_synthese][$regle_nom]['somme'] >= $this->table_potentiel[$groupe_synthese][$regle_nom]['limit']);
                            $task->addRestriction(new Simplex\Restriction($this->addemptycepage($categories[$regle['category']], $categories['cepages_couleur'], $regle['limit'] * -1), Simplex\Restriction::TYPE_GOE, 0));
                        }elseif ($regle['sens'] == '<=') {
                            $this->table_potentiel[$groupe_synthese][$regle_nom]['res'] = ($this->table_potentiel[$groupe_synthese][$regle_nom]['somme'] <= $this->table_potentiel[$groupe_synthese][$regle_nom]['limit']);
                            $task->addRestriction(new Simplex\Restriction($this->addemptycepage($categories[$regle['category']], $categories['cepages_couleur'], $regle['limit'] * -1), Simplex\Restriction::TYPE_LOE, 0));
                        }
                        break;
                    case 'ProportionChaque':
                        $this->table_potentiel[$groupe_synthese][$regle_nom]['somme'] = 0;
                        $this->table_potentiel[$groupe_synthese][$regle_nom]['limit'] = $encepagement * $regle['limit'];
                        $this->table_potentiel[$groupe_synthese][$regle_nom]['res'] = true;
                        foreach(array_keys($categories['cepages_principaux']) as $c) {
                            if (!isset($categories[$regle['category']][$c])) {
                                continue;
                            }
                            $this->table_potentiel[$groupe_synthese][$regle_nom]['somme'] .= $categories[$regle['category']][$c].'|';
                            if ($regle['sens'] == '>=') {
                                $this->table_potentiel[$groupe_synthese][$regle_nom]['res'] &= ($categories[$regle['category']][$c] >=  $this->table_potentiel[$groupe_synthese][$regle_nom]['limit']);
                                $task->addRestriction(new Simplex\Restriction($this->addemptycepage([$c => $categories[$regle['category']][$c]], $categories['cepages_couleur'], $regle['limit'] * -1), Simplex\Restriction::TYPE_GOE, 0));
                            }elseif ($regle['sens'] == '<=') {
                                $this->table_potentiel[$groupe_synthese][$regle_nom]['res'] &= ($categories[$regle['category']][$c] <=  $this->table_potentiel[$groupe_synthese][$regle_nom]['limit']);
                                $task->addRestriction(new Simplex\Restriction($this->addemptycepage([$c => $categories[$regle['category']][$c]], $categories['cepages_couleur'], $regle['limit'] * -1), Simplex\Restriction::TYPE_LOE, 0));
                            }
                        }
                        break;
                    default:
                        throw new sfException('Fonction de Potentiel de production "'.$regle['fonction'].'" non gérée');
                }
                $pprule = new PotentielProductionRule($ppproduit, $regle_nom, $this->table_potentiel[$groupe_synthese][$regle_nom]);
                $ppproduit->addRule($pprule);
            }
            foreach(array_keys($categories['cepages_couleur']) as $c) {
                if ($categories['cepages_couleur'][$c]) {
                    $task->addRestriction(new Simplex\Restriction($this->addemptycepage([$c => $categories['cepages_couleur'][$c]], $categories['cepages_couleur']), Simplex\Restriction::TYPE_LOE, $categories['cepages_couleur'][$c]));
                }
            }

            $solver = new Simplex\Solver($task);
            $solution = $solver->getSolution();
            if ($solution) {
                $optimum = $solver->getSolutionValue($solution);
                $this->potentiel_de_production[$groupe_synthese] = round($optimum->toFloat(), 5);
            }else{
                $this->potentiel_de_production[$groupe_synthese] = "IMPOSSIBLE";
            }
            if (!$potentiel_sans_blocant) {
                $this->potentiel_de_production[$groupe_synthese] = "IMPOSSIBLE";
            }
            if ($potentiel_has_desactive) {
                $this->potentiel_de_production[$groupe_synthese] = round(array_sum($categories['cepages_couleur']), 5);
                $encepagement = array_sum($categories['cepages_couleur']);
            }
            $this->encepagement[$groupe_synthese] = round($encepagement, 5);
            $ppproduit->setSuperficieMax($this->potentiel_de_production[$groupe_synthese]);
            $ppproduit->setSuperficieEncepagement($this->encepagement[$groupe_synthese]);
        }
    }

    private function addemptycepage($original, $keys, $value = 0) {
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

    public function getProduits() {
        return array_values($this->produits);
    }

}

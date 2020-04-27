<?php
class PotentielProductionProvenceGenerator extends PotentielProductionGenerator
{
    protected $identificationParcellaire;
    
    public function __construct($identifiant_or_etablissement)
    {
        parent::__construct($identifiant_or_etablissement);
        $this->identificationParcellaire = ParcellaireAffectationClient::getInstance()->getLast($this->etablissement->identifiant);
        if (!$this->identificationParcellaire) {
            $this->identificationParcellaire = ParcellaireIntentionAffectationClient::getInstance()->getLast($this->etablissement->identifiant);
        }
    }
    public function infos() 
    {
        $infos = "** PotentielProductionProvenceGenerator **\n\n".parent::infos();
        return  ($this->identificationParcellaire)? $infos.' - Identification parcellaire : '.$this->identificationParcellaire->_id."\n" : $infos." - Identification parcellaire : null\n";
    }
    
    public function getCepages($lieu = null, $couleur = null)
    {
        if ($lieu && !in_array($lieu, ['SVI', 'FRE', 'LLO', 'PIE', 'NDA'])) {
            throw new Exception("Lieu $lieu inconnu.");
        }
        if ($couleur && !in_array($couleur, ['rouge', 'rose', 'blanc'])) {
            throw new Exception("Couleur $couleur inconnu.");
        }
        if ($lieu && !$couleur) {
            throw new Exception ("La DGC $lieu necessite de préciser la couleur pour obtenir les cépages");
        }
        if ($lieu == 'SVI' && $couleur == 'rouge') {
            return [
                'principaux' => ["GRENACHE N", "SYRAH N", "CINSAUT N"],
                'secondairesNoirs' => ["CARIGNAN N", "CABERNET SAUVIGNON N", "MOURVEDRE N"],
                'secondairesBlancs' => ["CLAIRETTE B", "SEMILLON B", "UGNI BLANC B", "VERMENTINO B"]
            ];
        } elseif ($lieu == 'SVI' && $couleur == 'rose') {
            return [
                'principaux' => ["GRENACHE N", "SYRAH N", "CINSAUT N"],
                'secondairesNoirs' => ["CARIGNAN N", "CABERNET SAUVIGNON N", "MOURVEDRE N"],
                'secondairesBlancs' => ["CLAIRETTE B", "SEMILLON B", "UGNI BLANC B", "VERMENTINO B"]
            ];
        } elseif ($lieu == 'SVI' && $couleur == 'blanc') {
            return [];
        } elseif ($lieu == 'FRE' && $couleur == 'rouge') {
            return [
                'principaux' => ["GRENACHE N", "SYRAH N", "MOURVEDRE N"],
                'secondairesNoirs' => [],
                'secondairesBlancs' => []
            ];
        } elseif ($lieu == 'FRE' && $couleur == 'rose') {
            return [
                'principaux' => ["GRENACHE N", "SYRAH N", "MOURVEDRE N", "TIBOUREN N"],
                'secondairesNoirs' => ["CINSAUT N"],
                'secondairesBlancs' => []
            ];
        } elseif ($lieu == 'FRE' && $couleur == 'blanc') {
            return [];
        } elseif ($lieu == 'LLO' && $couleur == 'rouge') {
            return [
                'principaux' => ["GRENACHE N", "SYRAH N", "MOURVEDRE N"],
                'secondairesNoirs' => ["CARIGNAN N", "CABERNET SAUVIGNON N"],
                'secondairesBlancs' => []
            ];
        } elseif ($lieu == 'LLO' && $couleur == 'rose') {
            return [
                'principaux' => ["GRENACHE N", "CINSAUT N"],
                'secondairesNoirs' => ["CARIGNAN N", "SYRAH N", "MOURVEDRE N", "TIBOUREN N"],
                'secondairesBlancs' => ["CLAIRETTE B", "SEMILLON B", "UGNI BLANC B", "VERMENTINO B"]
            ];
        } elseif ($lieu == 'LLO' && $couleur == 'blanc') {
            return [
                'principaux' => ["VERMENTINO B"],
                'secondairesNoirs' => [],
                'secondairesBlancs' => ["CLAIRETTE B", "SEMILLON B", "UGNI BLANC B"]
            ];
        } elseif ($lieu == 'PIE' && $couleur == 'rouge') {
            return [
                'principaux' => ["GRENACHE N", "SYRAH N", "MOURVEDRE N"],
                'secondairesNoirs' => ["CARIGNAN N", "CABERNET SAUVIGNON N"],
                'secondairesBlancs' => []
            ];
        } elseif ($lieu == 'PIE' && $couleur == 'rose') {
            return [
                'principaux' => ["GRENACHE N", "SYRAH N", "CINSAUT N"],
                'secondairesNoirs' => ["MOURVEDRE N", "TIBOUREN N"],
                'secondairesBlancs' => ["CLAIRETTE B", "SEMILLON B", "UGNI BLANC B", "VERMENTINO B"]
            ];
        } elseif ($lieu == 'PIE' && $couleur == 'blanc') {
            return [];
        } elseif ($lieu == 'NDA') {
            return [];
        } else {
            return [
                'principaux' => ["GRENACHE N", "SYRAH N", "MOURVEDRE N", "TIBOUREN N", "CINSAUT N"],
                'secondairesNoirs' => ["CARIGNAN N", "CABERNET SAUVIGNON N", "CALITOR NOIR N", "BARBAROUX RS"],
                'secondairesBlancs' => ["CLAIRETTE B", "SEMILLON B", "UGNI BLANC B", "VERMENTINO B"]
            ];
        }
        return $cepages;
    }
    
    protected function aggSuperficesByCepages($parcelles, $cepages)
    {
        $agg = ['principaux' => ['TOTAL' => 0], 'secondairesnoirs' => ['TOTAL' => 0], 'secondairesblancs' => ['TOTAL' => 0], 'TOTAL' => 0];
        foreach ($parcelles as $parcelle) {
            if (!$this->respecteReglesEncepagement($parcelle)) {
                continue;
            }
            if (in_array($parcelle->cepage, $cepages['principaux'])) {
                if (!isset($agg['principaux'][$parcelle->cepage])) {
                    $agg['principaux'][$parcelle->cepage] = 0;
                }
                $agg['principaux'][$parcelle->cepage] += ($parcelle->exist('superficie_affectation') && $parcelle->superficie_affectation)? $parcelle->superficie_affectation : $parcelle->superficie;
                $agg['principaux']['TOTAL'] += ($parcelle->exist('superficie_affectation') && $parcelle->superficie_affectation)? $parcelle->superficie_affectation : $parcelle->superficie;
            } elseif (in_array($parcelle->cepage, $cepages['secondairesNoirs'])) {
                if (!isset($agg['secondairesnoirs'][$parcelle->cepage])) {
                    $agg['secondairesnoirs'][$parcelle->cepage] = 0;
                }
                $agg['secondairesnoirs'][$parcelle->cepage] += ($parcelle->exist('superficie_affectation') && $parcelle->superficie_affectation)? $parcelle->superficie_affectation : $parcelle->superficie;
                $agg['secondairesnoirs']['TOTAL'] += ($parcelle->exist('superficie_affectation') && $parcelle->superficie_affectation)? $parcelle->superficie_affectation : $parcelle->superficie;
            } elseif (in_array($parcelle->cepage, $cepages['secondairesBlancs'])) {
                if (!isset($agg['secondairesblancs'][$parcelle->cepage])) {
                    $agg['secondairesblancs'][$parcelle->cepage] = 0;
                }
                $agg['secondairesblancs'][$parcelle->cepage] += ($parcelle->exist('superficie_affectation') && $parcelle->superficie_affectation)? $parcelle->superficie_affectation : $parcelle->superficie;
                $agg['secondairesblancs']['TOTAL'] += ($parcelle->exist('superficie_affectation') && $parcelle->superficie_affectation)? $parcelle->superficie_affectation : $parcelle->superficie;
            }
            $agg['TOTAL'] += ($parcelle->exist('superficie_affectation') && $parcelle->superficie_affectation)? $parcelle->superficie_affectation : $parcelle->superficie;
        }
        return $agg;
    }
    
    protected function getDgcSuperficiesByCepage($dgc)
    {
        $aggCepage = [];
        foreach ($dgc as $couleur => $agg) {
            foreach ($agg as $parcelles) {
                foreach ($parcelles as $cepage => $superficie) {
                    if ($cepage == 'TOTAL') {
                        continue;
                    }
                    if (!isset($aggCepage[$cepage]) || $aggCepage[$cepage] > $superficie) {
                        $aggCepage[$cepage] = $superficie;
                    }
                }
            }
        }
        return $aggCepage;
    }
    
    protected function getSuperfices()
    {
        $superficies = [];
        // CDP + DGC
        if ($dgcs = $this->identificationParcellaire->getDgc()) {
            $parcelles = $this->identificationParcellaire->getParcelles();
            foreach ($dgcs as $dgcId => $dgcLib) {
                $superficies[$dgcId] = [];
                foreach (array('rouge', 'rose', 'blanc') as $couleur) {
                    if ($cepages = $this->getCepages($dgcId, $couleur)) {
                        $superficies[$dgcId][$couleur] = $this->aggSuperficesByCepages($parcelles, $cepages);
                    }
                }
            }
        }
        // CDP Generique
        $cdp = $this->aggSuperficesByCepages($this->parcellaire->getParcelles(), $this->getCepages());
        if ($superficies) {
            $soustraire = [];
            foreach ($superficies as $id => $dgc) {
                $soustraire[$id] = $this->getDgcSuperficiesByCepage($dgc);
            }
            foreach (array('principaux', 'secondairesnoirs', 'secondairesblancs') as $cat) {
                foreach ($cdp[$cat] as $k => $v) {
                    foreach ($soustraire as $cepages) {
                        if (isset($cepages[$k])) {
                            $cdp[$cat][$k] = (($v - $cepages[$k]) > 0)? round($v - $cepages[$k], 4) : 0;
                            $cdp[$cat]['TOTAL'] = (($cdp[$cat]['TOTAL'] - $cepages[$k]) > 0)? round($cdp[$cat]['TOTAL'] - $cepages[$k], 4) : 0;
                        }
                    }
                }
            }
            $cdp['TOTAL'] = round($cdp['principaux']['TOTAL'] + $cdp['secondairesblancs']['TOTAL'] + $cdp['secondairesnoirs']['TOTAL'], 4);
        }
        $superficies['CDP'] = $cdp;
        return $superficies;
    }
    
    public function getRevendicables()
    {
        $superficies = $this->getSuperfices();
        $revendicables = [];
        $revendicables['CDP'] = $this->calculateRevendicable($superficies['CDP']);
        return $revendicables;
    }
    
    protected function calculateRevendicable($superficies)
    {
        $revendicables = array();
        // Regle des 2 cepages principaux min.
        if (count($superficies['principaux']) - 1 < 2) {
            foreach ($superficies['principaux'] as $cepage => $superficie) {
               $revendicables['principaux'] = array('TOTAL' => 0);
            }
        } else {
            // Regle des 90%
            $maxSuperficeCepagePrincipal = round(($superficies['TOTAL']*0.9), 4);
            foreach ($superficies['principaux'] as $cepage => $superficie) {
                $revendicables['principaux'][$cepage] = $superficie;
                if ($cepage == 'TOTAL') {
                    continue;
                }
                if ($superficie > $maxSuperficeCepagePrincipal) {
                    $total = 0;
                    foreach ($superficies['principaux'] as $c => $s) {
                        if (in_array($c, array('TOTAL', $cepage))) {
                            continue;
                        }
                        $total += $s;
                    }
                    $revendicables['principaux'][$cepage] = round(($total+$superficies['secondairesblancs']['TOTAL']+$superficies['secondairesnoirs']['TOTAL'])*9, 4);
                    $revendicables['principaux']['TOTAL'] = round($total + $revendicables['principaux'][$cepage], 4);
                }
            }
            
            $revendicables['secondairesblancs']['TOTAL'] = $superficies['secondairesblancs']['TOTAL'];
            $revendicables['TOTAL'] = round($superficies['principaux']['TOTAL'] + $superficies['secondairesblancs']['TOTAL'] + $superficies['secondairesnoirs']['TOTAL'], 4);
        }
        // Regle des 70%
        $maxSuperficeSecondairesnoirs = round($revendicables['principaux']['TOTAL']*30/70,4);
        $revendicables['secondairesnoirs']['TOTAL'] = ($superficies['secondairesnoirs']['TOTAL'] > $maxSuperficeSecondairesnoirs)? $maxSuperficeSecondairesnoirs : $superficies['secondairesnoirs']['TOTAL'];
        if ($revendicables['secondairesnoirs']['TOTAL'] == $maxSuperficeSecondairesnoirs) {
            $revendicables['secondairesblancs']['TOTAL'] = 0;
        } else {
            $minSuperficeSecondairesblancs = round($revendicables['principaux']['TOTAL']*10/70,4);
            $maxSuperficeSecondairesblancs = round($revendicables['principaux']['TOTAL']*20/70,4);
            if ($revendicables['secondairesnoirs']['TOTAL'] <= $minSuperficeSecondairesblancs) {
                $revendicables['secondairesblancs']['TOTAL'] = $maxSuperficeSecondairesblancs;
            } else {
                $s = $maxSuperficeSecondairesblancs - $revendicables['secondairesnoirs']['TOTAL'];
                $revendicables['secondairesblancs']['TOTAL'] = ($s > 0)? round($s, 4) : 0;
            }
            if ($revendicables['secondairesblancs']['TOTAL'] > $superficies['secondairesblancs']['TOTAL']) {
                $revendicables['secondairesblancs']['TOTAL'] = round($superficies['secondairesblancs']['TOTAL'],4);
            }
        }
        $revendicables['TOTAL'] = round($revendicables['principaux']['TOTAL'] + $revendicables['secondairesblancs']['TOTAL'] + $revendicables['secondairesnoirs']['TOTAL'], 4);
        // Regle des 20%
        if (($revendicables['secondairesblancs']['TOTAL'] * 100 / $revendicables['TOTAL']) > 20) {
            $revendicables['secondairesblancs']['TOTAL'] = round(($revendicables['principaux']['TOTAL'] + $revendicables['secondairesnoirs']['TOTAL']) / 4, 4);
            $revendicables['TOTAL'] = round($revendicables['principaux']['TOTAL'] + $revendicables['secondairesblancs']['TOTAL'] + $revendicables['secondairesnoirs']['TOTAL'], 4);
        }
        // Regle des 10%
        $superficiesBlancsSaisies = round($superficies['secondairesblancs']['TOTAL'],4);
        $superficieVermentinoBSaisie = (isset($superficies['secondairesblancs']['VERMENTINO B']))? round($superficiesBlancsSaisies - $superficies['secondairesblancs']['VERMENTINO B'],4) : 0;
        $superficiesAutresBlancsSaisies = round($superficiesBlancsSaisies - $superficieVermentinoBSaisie, 4);
        
        $maxDixPourcentRevendicable = round(($revendicables['principaux']['TOTAL'] + $revendicables['secondairesnoirs']['TOTAL'] + $superficieVermentinoBSaisie) / 9, 4);
        $dixPourcentRevendicable = ($superficiesAutresBlancsSaisies > $maxDixPourcentRevendicable)? $maxDixPourcentRevendicable : $superficiesAutresBlancsSaisies;
        if ($dixPourcentRevendicable > $revendicables['secondairesblancs']['TOTAL']) {
            $dixPourcentRevendicable = $revendicables['secondairesblancs']['TOTAL'];
        }
        
        $maxVermentinoBRevendicable = round($revendicables['secondairesblancs']['TOTAL']-$dixPourcentRevendicable, 4);
        $vermentinoBRevendicable = ($superficieVermentinoBSaisie > $maxVermentinoBRevendicable)? $maxVermentinoBRevendicable : $superficieVermentinoBSaisie;
        $revendicables['secondairesblancs']['TOTAL'] = round($vermentinoBRevendicable + $dixPourcentRevendicable, 4);
        $revendicables['secondairesblancs']['VERMENTINO B'] = $vermentinoBRevendicable;
        $revendicables['secondairesblancs']['AUTRES'] = $dixPourcentRevendicable;
        $revendicables['TOTAL'] = round($revendicables['principaux']['TOTAL'] + $revendicables['secondairesblancs']['TOTAL'] + $revendicables['secondairesnoirs']['TOTAL'], 4);
        $revendicables['BLANC'] = round(round($superficies['secondairesblancs']['TOTAL'], 4) - $revendicables['secondairesblancs']['TOTAL'], 4);
        return $revendicables;
    }
    
    protected function respecteReglesEncepagement($parcelle)
    {
        $dgc = null;
        if ($parcelle->exist('affectee') && $parcelle->affectee) {
            $dgc = $parcelle->getDgcLibelle();
        } elseif ($parcelle->exist('affectation') && $parcelle->affectation) {
            $dgc = $parcelle->getDgcLibelle();
        }
        if (in_array($parcelle->cepage, array('CALITOR NOIR N', 'BARBAROUX RS'))) {
            if ($dgc) {
                return false;
            }
            if (!$parcelle->campagne_plantation || intval(substr($parcelle->campagne_plantation, -4)) > 1994) {
                return false;
            }
        }
        return true;
    }
}
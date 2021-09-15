<?php
class PotentielProductionProvenceGenerator extends PotentielProductionGenerator
{
    protected $identificationParcellaire;
    public static $categories = ['principaux', 'secondairesNoirs', 'secondairesBlancsVermentino', 'secondairesBlancsAutres'];
    protected $isPetiteSurface;
    
    public function __construct($identifiant_or_etablissement)
    {
        if ($identifiant_or_etablissement) {
            parent::__construct($identifiant_or_etablissement);
            $this->identificationParcellaire = ParcellaireAffectationClient::getInstance()->getLast($this->etablissement->identifiant);
            if (!$this->identificationParcellaire) {
                $this->identificationParcellaire = ParcellaireIntentionAffectationClient::getInstance()->getLast($this->etablissement->identifiant);
            }
            $cdp = $this->aggSuperficesByCepages($this->parcellaire->getParcelles(), $this->getCepages());
            if ($this->etablissement->isViticulteur() && $cdp['TOTAL'] < 1.5) {
                $this->isPetiteSurface = true;
            }
        }
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
            throw new Exception ("La DGC $lieu necessite de préciser la couleur pour obtenir les cépages.");
        }
        if ($lieu == 'SVI' && $couleur == 'rouge') {
            return [
                'principaux' => ['GRENACHE N', 'SYRAH N', 'CINSAUT N'],
                'secondaires' => ['CARIGNAN N', 'CABERNET SAUVIGNON N', 'MOURVEDRE N', 'CLAIRETTE B', 'SEMILLON B', 'UGNI BLANC B', 'VERMENTINO B']
            ];
        } elseif ($lieu == 'SVI' && $couleur == 'rose') {
            return [
                'principaux' => ['GRENACHE N', 'SYRAH N', 'CINSAUT N'],
                'secondaires' => ['CARIGNAN N', 'CABERNET SAUVIGNON N', 'MOURVEDRE N', 'CLAIRETTE B', 'SEMILLON B', 'UGNI BLANC B', 'VERMENTINO B']
            ];
        } elseif ($lieu == 'SVI' && $couleur == 'blanc') {
            return [];
        } elseif ($lieu == 'FRE' && $couleur == 'rouge') {
            return [
                'principaux' => ['GRENACHE N', 'SYRAH N', 'MOURVEDRE N'],
                'secondaires' => []
            ];
        } elseif ($lieu == 'FRE' && $couleur == 'rose') {
            return [
                'principaux' => ['GRENACHE N', 'SYRAH N', 'MOURVEDRE N', 'TIBOUREN N'],
                'secondaires' => ['CINSAUT N']
            ];
        } elseif ($lieu == 'FRE' && $couleur == 'blanc') {
            return [];
        } elseif ($lieu == 'LLO' && $couleur == 'rouge') {
            return [
                'principaux' => ['GRENACHE N', 'SYRAH N', 'MOURVEDRE N'],
                'secondaires' => ['CARIGNAN N', 'CABERNET SAUVIGNON N']
            ];
        } elseif ($lieu == 'LLO' && $couleur == 'rose') {
            return [
                'principaux' => ['GRENACHE N', 'CINSAUT N'],
                'secondaires' => ['CARIGNAN N', 'SYRAH N', 'MOURVEDRE N', 'TIBOUREN N', 'CLAIRETTE B', 'SEMILLON B', 'UGNI BLANC B', 'VERMENTINO B']
            ];
        } elseif ($lieu == 'LLO' && $couleur == 'blanc') {
            return [
                'principaux' => ['VERMENTINO B'],
                'secondaires' => ['CLAIRETTE B', 'SEMILLON B', 'UGNI BLANC B']
            ];
        } elseif ($lieu == 'PIE' && $couleur == 'rouge') {
            return [
                'principaux' => ['GRENACHE N', 'SYRAH N', 'MOURVEDRE N'],
                'secondaires' => ['CARIGNAN N', 'CABERNET SAUVIGNON N']
            ];
        } elseif ($lieu == 'PIE' && $couleur == 'rose') {
            return [
                'principaux' => ['GRENACHE N', 'SYRAH N', 'CINSAUT N'],
                'secondaires' => ['MOURVEDRE N', 'TIBOUREN N', 'CLAIRETTE B', 'SEMILLON B', 'UGNI BLANC B', 'VERMENTINO B']
            ];
        } elseif ($lieu == 'PIE' && $couleur == 'blanc') {
            return [];
        } elseif ($lieu == 'NDA' && $couleur == 'rouge') {
            return [
                'principaux' => ['GRENACHE N', 'SYRAH N', 'CINSAUT N'],
                'secondaires' => ['CARIGNAN N', 'CABERNET SAUVIGNON N', 'MOURVEDRE N', 'TIBOUREN N', 'CLAIRETTE B', 'SEMILLON B', 'UGNI BLANC B', 'VERMENTINO B']
            ];
        } elseif ($lieu == 'NDA' && $couleur == 'rose') {
            return [
                'principaux' => ['GRENACHE N', 'SYRAH N', 'CINSAUT N'],
                'secondaires' => ['CARIGNAN N', 'CABERNET SAUVIGNON N', 'MOURVEDRE N', 'TIBOUREN N', 'CLAIRETTE B', 'SEMILLON B', 'UGNI BLANC B', 'VERMENTINO B']
            ];
        } elseif ($lieu == 'NDA' && $couleur == 'blanc') {
            return [];
        } else {
            return [
                'principaux' => ['GRENACHE N', 'SYRAH N', 'MOURVEDRE N', 'TIBOUREN N', 'CINSAUT N'],
                'secondaires' => ['CARIGNAN N', 'CABERNET SAUVIGNON N', 'CALITOR NOIR N', 'BARBAROUX RS', 'CLAIRETTE B', 'SEMILLON B', 'UGNI BLANC B', 'VERMENTINO B']
            ];
        }
    }
    
    public function getDonnees($superficies = null)
    {
        $superficies = ($superficies)? $superficies : $this->getSuperficies();
        $revendicables = [];
        foreach ($superficies as $appellation => $items) {
            if($appellation == 'CDP') { $items = [0 => $items]; }
            foreach ($items as $couleur => $superficie) {
                if ($couleur) {
                    $fct = 'calculateRevendicable'.strtoupper($appellation).ucfirst(strtolower($couleur));
                    $revendicables[$appellation][$couleur] = ($this->isPetiteSurface)? $this->calculateRevendicablePetiteSurface($superficies[$appellation][$couleur]) : $this->$fct($superficies[$appellation][$couleur]);
                } else {
                    $revendicables[$appellation] = ($this->isPetiteSurface)? $this->calculateRevendicablePetiteSurface($superficies[$appellation]) : $this->calculateRevendicableCDP($superficies[$appellation]);
                }
            }
        }
        return $revendicables;
    }
    
    public function getSuperficies()
    {
        $superficies = [];
        // CDP + DGC
        if ($this->identificationParcellaire) {
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
        }
        // CDP Generique
        $cdp = $this->aggSuperficesByCepages($this->parcellaire->getParcelles(), $this->getCepages());
        if ($superficies) {
            $soustraire = [];
            foreach ($superficies as $id => $dgc) {
                $soustraire[$id] = $this->getDgcSuperficiesByCepage($dgc);
            }
            foreach (self::$categories as $cat) {
                foreach ($cdp[$cat] as $k => $v) {
                    foreach ($soustraire as $cepages) {
                        if (isset($cepages[$k])) {
                            $cdp[$cat][$k] = (($v - $cepages[$k]) > 0)? round($v - $cepages[$k], 4) : 0;
                            $cdp[$cat]['TOTAL'] = (($cdp[$cat]['TOTAL'] - $cepages[$k]) > 0)? round($cdp[$cat]['TOTAL'] - $cepages[$k], 4) : 0;
                        }
                    }
                }
            }
            $cdp['TOTAL'] = 0;
            foreach (self::$categories as $cat) {
                $cdp['TOTAL'] += round($cdp[$cat]['TOTAL'], 4);
            }
        }
        $superficies['CDP'] = $cdp;
        return $superficies;
    }
    
    protected function aggSuperficesByCepages($parcelles, $cepages)
    {
        $agg = ['TOTAL' => 0];
        foreach (self::$categories as $cat) {
            $agg[$cat]['TOTAL'] = 0;
        }
        foreach ($parcelles as $parcelle) {
            if (!$this->respecteReglesEncepagement($parcelle)) {
                continue;
            }
            if (in_array($parcelle->cepage, $cepages['principaux'])) {
                $key = 'principaux';
            } elseif (in_array($parcelle->cepage, $cepages['secondaires'])) {
                if ($parcelle->cepage == 'VERMENTINO B') {
                    $key = 'secondairesBlancsVermentino';
                } elseif (strtoupper(substr(trim($parcelle->cepage), -1)) == 'B') {
                    $key = 'secondairesBlancsAutres';
                } else {
                    $key = 'secondairesNoirs';
                }
            } else {
                continue;
            }
            if (!isset($agg[$key][$parcelle->cepage])) {
                $agg[$key][$parcelle->cepage] = 0;
            }
            $agg[$key][$parcelle->cepage] += ($parcelle->exist('superficie_affectation') && $parcelle->superficie_affectation)? $parcelle->superficie_affectation : $parcelle->superficie;
            $agg[$key]['TOTAL'] += ($parcelle->exist('superficie_affectation') && $parcelle->superficie_affectation)? $parcelle->superficie_affectation : $parcelle->superficie;
            $agg['TOTAL'] += ($parcelle->exist('superficie_affectation') && $parcelle->superficie_affectation)? $parcelle->superficie_affectation : $parcelle->superficie;
        }
        return $agg;
    }
    
    public function calculateRevendicableCDP($superficies)
    {
        $revendicables = [];
        // Affectation renvendicables principaux
        $revendicables['principaux'] = $this->regleNbCepageMin_GetRevendicable($superficies['principaux'], 2);
        if ($revendicables['principaux'] > 0) {
            $revendicables['principaux'] = $this->reglePourcentageCepageMax_GetRevendicable($superficies['principaux'], $superficies['TOTAL'], 90);
        }
        // Affectation revendicables secondaires
        $revendicableSecondairesMax = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 30/70);
        $revendicableSecondairesBlancsVermontinoMax = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 20/70);
        $revendicableSecondairesBlancsAutresMax = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 10/70);
        // Affectation revendicables secondaires noirs
        $revendicables['secondairesNoirs'] = 0;
        if (isset($superficies['secondairesNoirs'])) {
            $revendicableSecondairesBlancsMax = 0;
            if ($superficies['secondairesNoirs']['TOTAL'] <= $revendicableSecondairesMax) {
                $revendicables['secondairesNoirs'] = $superficies['secondairesNoirs']['TOTAL'];
                $revendicableSecondairesBlancsMax = round($revendicableSecondairesMax - $superficies['secondairesNoirs']['TOTAL'], 4);
            } else {
                $revendicables['secondairesNoirs'] = $revendicableSecondairesMax;
            }
        }
        // Affectation revendicables secondaires blancs vermontino
        $revendicables['secondairesBlancsVermentino'] = 0;
        if (isset($superficies['secondairesBlancsVermentino']['TOTAL'])) {
            $revendicables['secondairesBlancsVermentino'] = $this->regleRevendicableAvecMax_GetRevendicable($revendicableSecondairesBlancsMax, $revendicableSecondairesBlancsVermontinoMax, $superficies['secondairesBlancsVermentino']['TOTAL']);
        }
        $revendicableSecondairesBlancsMax = round($revendicableSecondairesBlancsMax - $revendicables['secondairesBlancsVermentino'], 4);
        // Affectation revendicables secondaires blancs autres
        $revendicables['secondairesBlancsAutres'] = 0;
        if (isset($superficies['secondairesBlancsAutres']['TOTAL'])) {
            $revendicables['secondairesBlancsAutres'] = $this->regleRevendicableAvecMax_GetRevendicable($revendicableSecondairesBlancsMax, $revendicableSecondairesBlancsAutresMax, $superficies['secondairesBlancsAutres']['TOTAL']);
        }
        return $this->generateResultRevendicabe($revendicables, $superficies);
    }

    public function calculateRevendicableSVIRose($superficies)
    {
        $revendicables = [];
        // Definition de l'encepagement max
        $encepagementTotal = $this->reglePourcentageCepageMin_GetEncepagementMax(["GRENACHE N", "SYRAH N"], $superficies, 50);
        // Affectation renvendicables principaux
        $revendicables['principaux'] = $this->regleNbCepageMin_GetRevendicable($superficies['principaux'], 2);
        if ($revendicables['principaux'] > 0) {
            $revendicables['principaux'] = $this->reglePourcentageCepageMax_GetRevendicable($superficies['principaux'], $superficies['TOTAL'], 80);
            $principaux = round($encepagementTotal*80/100,4);
            if ($principaux < $revendicables['principaux']) {
                $revendicables['principaux'] = $principaux;
            }
        }
        // Affectation revendicables secondaires
        $revendicableSecondairesMax = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 20/80);
        $revendicableSecondairesBlancsVermontinoMax = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 20/80);
        $revendicableSecondairesBlancsAutresMax = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 10/80);
        // Affectation revendicables secondaires noirs
        $revendicableSecondairesBlancsMax = 0;
        if ($superficies['secondairesNoirs']['TOTAL'] <= $revendicableSecondairesMax) {
            $revendicables['secondairesNoirs'] = $superficies['secondairesNoirs']['TOTAL'];
            $revendicableSecondairesBlancsMax = round($revendicableSecondairesMax - $superficies['secondairesNoirs']['TOTAL'], 4);
        } else {
            $revendicables['secondairesNoirs'] = $revendicableSecondairesMax;
        }
        // Affectation revendicables secondaires blancs vermontino
        $revendicables['secondairesBlancsVermentino'] = $this->regleRevendicableAvecMax_GetRevendicable($revendicableSecondairesBlancsMax, $revendicableSecondairesBlancsVermontinoMax, $superficies['secondairesBlancsVermentino']['TOTAL']);
        $revendicableSecondairesBlancsMax = round($revendicableSecondairesBlancsMax - $revendicables['secondairesBlancsVermentino'], 4);
        // Affectation revendicables secondaires blancs autres
        $revendicables['secondairesBlancsAutres'] = $this->regleRevendicableAvecMax_GetRevendicable($revendicableSecondairesBlancsMax, $revendicableSecondairesBlancsAutresMax, $superficies['secondairesBlancsAutres']['TOTAL']);
        return $this->generateResultRevendicabe($revendicables, $superficies);
    }

    public function calculateRevendicableSVIRouge($superficies)
    {
        $revendicables = [];
        // Definition de l'encepagement max
        $encepagementTotal = $this->reglePourcentageCepageMin_GetEncepagementMax(["GRENACHE N", "SYRAH N"], $superficies, 50);
        // Affectation renvendicables principaux
        $revendicables['principaux'] = $this->regleNbCepageMin_GetRevendicable($superficies['principaux'], 2);
        if ($revendicables['principaux'] > 0) {
            $revendicables['principaux'] = $this->reglePourcentageCepageMax_GetRevendicable($superficies['principaux'], $superficies['TOTAL'], 80);
            $principaux = round($encepagementTotal*80/100,4);
            if ($principaux < $revendicables['principaux']) {
                $revendicables['principaux'] = $principaux;
            }
        }
        // Affectation revendicables secondaires
        $revendicableSecondairesMax = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 20/80);
        $revendicableSecondairesBlancsVermontinoMax = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 20/80);
        $revendicableSecondairesBlancsAutresMax = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 10/80);
        // Affectation revendicables pour le cabernet sauvignon
        if (isset($superficies['secondairesNoirs']['CABERNET SAUVIGNON N'])) {
            $superficies['secondairesNoirs']['TOTAL'] -= $superficies['secondairesNoirs']['CABERNET SAUVIGNON N'];
            $superficies['secondairesNoirs']['CABERNET SAUVIGNON N'] = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 10/80, $superficies['secondairesNoirs']['CABERNET SAUVIGNON N']);
            $superficies['secondairesNoirs']['TOTAL'] += $superficies['secondairesNoirs']['CABERNET SAUVIGNON N'];
        }
        // Affectation revendicables secondaires noirs
        $revendicableSecondairesBlancsMax = 0;
        if ($superficies['secondairesNoirs']['TOTAL'] <= $revendicableSecondairesMax) {
            $revendicables['secondairesNoirs'] = $superficies['secondairesNoirs']['TOTAL'];
            $revendicableSecondairesBlancsMax = round($revendicableSecondairesMax - $superficies['secondairesNoirs']['TOTAL'], 4);
        } else {
            $revendicables['secondairesNoirs'] = $revendicableSecondairesMax;
        }
        // Affectation revendicables secondaires blancs vermontino
        $revendicables['secondairesBlancsVermentino'] = $this->regleRevendicableAvecMax_GetRevendicable($revendicableSecondairesBlancsMax, $revendicableSecondairesBlancsVermontinoMax, $superficies['secondairesBlancsVermentino']['TOTAL']);
        $revendicableSecondairesBlancsMax = round($revendicableSecondairesBlancsMax - $revendicables['secondairesBlancsVermentino'], 4);
        // Affectation revendicables secondaires blancs autres
        $revendicables['secondairesBlancsAutres'] = $this->regleRevendicableAvecMax_GetRevendicable($revendicableSecondairesBlancsMax, $revendicableSecondairesBlancsAutresMax, $superficies['secondairesBlancsAutres']['TOTAL']);
        return $this->generateResultRevendicabe($revendicables, $superficies);
    }
    
    public function calculateRevendicableFRERouge($superficies)
    {
        $revendicables = [];
        // Affectation renvendicables principaux
        $revendicables['principaux'] = $this->reglePourcentageCepageMax_GetRevendicable($superficies['principaux'], $superficies['TOTAL'], 60);
        return $this->generateResultRevendicabe($revendicables, $superficies);
    }
    
    public function calculateRevendicableFRERose($superficies)
    {
        $revendicables = [];
        // Definition de l'encepagement max
        $encepagementTotal = $this->reglePourcentageCepageMin_GetEncepagementMax(["TIBOUREN N"], $superficies, 20);
        // Affectation renvendicables principaux
        $revendicables['principaux'] = $this->regleNbCepageMin_GetRevendicable($superficies['principaux'], 2);
        if ($revendicables['principaux'] > 0) {
            $revendicables['principaux'] = $this->reglePourcentageCepageMax_GetRevendicable($superficies['principaux'], $superficies['TOTAL'], 60);
            $principaux = round($encepagementTotal*80/100,4);
            if ($principaux < $revendicables['principaux']) {
                $revendicables['principaux'] = $principaux;
            }
        }
        // Affectation revendicables secondaires noirs
        $revendicableSecondairesMax = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 20/80);
        if ($superficies['secondairesNoirs']['TOTAL'] <= $revendicableSecondairesMax) {
            $revendicables['secondairesNoirs'] = $superficies['secondairesNoirs']['TOTAL'];
        } else {
            $revendicables['secondairesNoirs'] = $revendicableSecondairesMax;
        }
        return $this->generateResultRevendicabe($revendicables, $superficies);
    }
    
    public function calculateRevendicableLLOBlanc($superficies)
    {
        $revendicables = [];
        // Affectation renvendicables principaux
        $revendicables['principaux'] = $superficies['principaux']['TOTAL'];
        // Affectation revendicables secondaires blancs autres
        $revendicableSecondairesMax = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 50/100);
        if ($superficies['secondairesBlancsAutres']['TOTAL'] <= $revendicableSecondairesMax) {
            $revendicables['secondairesBlancsAutres'] = $superficies['secondairesBlancsAutres']['TOTAL'];
        } else {
            $revendicables['secondairesBlancsAutres'] = $revendicableSecondairesMax;
        }
        return $this->generateResultRevendicabe($revendicables, $superficies);
    }
    
    public function calculateRevendicableLLORouge($superficies)
    {
        $revendicables = [];
        // Definition de l'encepagement max
        $encepagementTotal = $this->reglePourcentageCepageMin_GetEncepagementMax(["GRENACHE N", "SYRAH N"], $superficies, 50);
        // Affectation renvendicables principaux
        $revendicables['principaux'] = $this->regleNbCepageMin_GetRevendicable($superficies['principaux'], 2);
        if ($revendicables['principaux'] > 0) {
            $revendicables['principaux'] = $this->reglePourcentageCepageMax_GetRevendicable($superficies['principaux'], $superficies['TOTAL'], 60);
            $principaux = round($encepagementTotal*80/100,4);
            if ($principaux < $revendicables['principaux']) {
                $revendicables['principaux'] = $principaux;
            }
        }
        // Affectation revendicables secondaires noirs
        $revendicableSecondairesMax = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 20/80);
        // Affectation revendicables pour le cabernet sauvignon
        if (isset($superficies['secondairesNoirs']['CABERNET SAUVIGNON N'])) {
            $superficies['secondairesNoirs']['TOTAL'] -= $superficies['secondairesNoirs']['CABERNET SAUVIGNON N'];
            $superficies['secondairesNoirs']['CABERNET SAUVIGNON N'] = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 10/80, $superficies['secondairesNoirs']['CABERNET SAUVIGNON N']);
            $superficies['secondairesNoirs']['TOTAL'] += $superficies['secondairesNoirs']['CABERNET SAUVIGNON N'];
        }
        if ($superficies['secondairesNoirs']['TOTAL'] <= $revendicableSecondairesMax) {
            $revendicables['secondairesNoirs'] = $superficies['secondairesNoirs']['TOTAL'];
        } else {
            $revendicables['secondairesNoirs'] = $revendicableSecondairesMax;
        }
        return $this->generateResultRevendicabe($revendicables, $superficies);
    }
    
    public function calculateRevendicableLLORose($superficies)
    {
        $revendicables = [];
        // Affectation renvendicables principaux
        $revendicables['principaux'] = $this->regleNbCepageMin_GetRevendicable($superficies['principaux'], 2);
        if ($revendicables['principaux'] > 0) {
            $revendicables['principaux'] = $this->reglePourcentageCepageMax_GetRevendicable($superficies['principaux'], $superficies['TOTAL'], 60);
        }
        // Affectation revendicables secondaires
        $revendicableSecondairesMax = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 20/80);
        $revendicableSecondairesBlancsVermontinoMax = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 20/80);
        $revendicableSecondairesBlancsAutresMax = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 10/80);
        // Affectation revendicables secondaires noirs
        $revendicableSecondairesBlancsMax = 0;
        if ($superficies['secondairesNoirs']['TOTAL'] <= $revendicableSecondairesMax) {
            $revendicables['secondairesNoirs'] = $superficies['secondairesNoirs']['TOTAL'];
            $revendicableSecondairesBlancsMax = round($revendicableSecondairesMax - $superficies['secondairesNoirs']['TOTAL'], 4);
        } else {
            $revendicables['secondairesNoirs'] = $revendicableSecondairesMax;
        }
        // Affectation revendicables secondaires blancs vermontino
        $revendicables['secondairesBlancsVermentino'] = $this->regleRevendicableAvecMax_GetRevendicable($revendicableSecondairesBlancsMax, $revendicableSecondairesBlancsVermontinoMax, $superficies['secondairesBlancsVermentino']['TOTAL']);
        $revendicableSecondairesBlancsMax = round($revendicableSecondairesBlancsMax - $revendicables['secondairesBlancsVermentino'], 4);
        // Affectation revendicables secondaires blancs autres
        $revendicables['secondairesBlancsAutres'] = $this->regleRevendicableAvecMax_GetRevendicable($revendicableSecondairesBlancsMax, $revendicableSecondairesBlancsAutresMax, $superficies['secondairesBlancsAutres']['TOTAL']);
        return $this->generateResultRevendicabe($revendicables, $superficies);
    }
    
    public function calculateRevendicablePIERouge($superficies)
    {
        $revendicables = [];
        // Affectation renvendicables principaux
        $revendicables['principaux'] = $this->regleNbCepageMin_GetRevendicable($superficies['principaux'], 2);
        if ($revendicables['principaux'] > 0) {
            $revendicables['principaux'] = $this->reglePourcentageCepageMax_GetRevendicable($superficies['principaux'], $superficies['TOTAL'], 80);
        }
         // Affectation revendicables secondaires noirs
        $revendicableSecondairesMax = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 20/80);
        if ($superficies['secondairesNoirs']['TOTAL'] <= $revendicableSecondairesMax) {
            $revendicables['secondairesNoirs'] = $superficies['secondairesNoirs']['TOTAL'];
        } else {
            $revendicables['secondairesNoirs'] = $revendicableSecondairesMax;
        }
        return $this->generateResultRevendicabe($revendicables, $superficies);
    }

    public function calculateRevendicablePIERose($superficies)
    {
        $revendicables = [];
        // Affectation renvendicables principaux
        $revendicables['principaux'] = $this->regleNbCepageMin_GetRevendicable($superficies['principaux'], 2);
        if ($revendicables['principaux'] > 0) {
            $revendicables['principaux'] = $this->reglePourcentageCepageMax_GetRevendicable($superficies['principaux'], $superficies['TOTAL'], 80);
        }
        // Affectation revendicables secondaires
        $revendicableSecondairesMax = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 20/80);
        $revendicableSecondairesBlancsVermontinoMax = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 20/80);
        $revendicableSecondairesBlancsAutresMax = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 10/80);
        // Affectation revendicables secondaires noirs
        $revendicableSecondairesBlancsMax = 0;
        if ($superficies['secondairesNoirs']['TOTAL'] <= $revendicableSecondairesMax) {
            $revendicables['secondairesNoirs'] = $superficies['secondairesNoirs']['TOTAL'];
            $revendicableSecondairesBlancsMax = round($revendicableSecondairesMax - $superficies['secondairesNoirs']['TOTAL'], 4);
        } else {
            $revendicables['secondairesNoirs'] = $revendicableSecondairesMax;
        }
        // Affectation revendicables secondaires blancs vermontino
        $revendicables['secondairesBlancsVermentino'] = $this->regleRevendicableAvecMax_GetRevendicable($revendicableSecondairesBlancsMax, $revendicableSecondairesBlancsVermontinoMax, $superficies['secondairesBlancsVermentino']['TOTAL']);
        $revendicableSecondairesBlancsMax = round($revendicableSecondairesBlancsMax - $revendicables['secondairesBlancsVermentino'], 4);
        // Affectation revendicables secondaires blancs autres
        $revendicables['secondairesBlancsAutres'] = $this->regleRevendicableAvecMax_GetRevendicable($revendicableSecondairesBlancsMax, $revendicableSecondairesBlancsAutresMax, $superficies['secondairesBlancsAutres']['TOTAL']);
        return $this->generateResultRevendicabe($revendicables, $superficies);
    }

    public function calculateRevendicableNDARouge($superficies)
    {
        $revendicables = [];
        // Affectation renvendicables principaux
        $revendicables['principaux'] = $this->regleNbCepageMin_GetRevendicable($superficies['principaux'], 2);
        if ($revendicables['principaux'] > 0) {
            $revendicables['principaux'] = $this->reglePourcentageCepageMax_GetRevendicable($superficies['principaux'], $superficies['TOTAL'], 80);
        }
        // Affectation revendicables secondaires
        $revendicableSecondairesMax = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 20/80);
        $revendicableSecondairesBlancsVermontinoMax = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 20/80);
        $revendicableSecondairesBlancsAutresMax = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 10/80);
        // Affectation revendicables pour le cabernet sauvignon
        if (isset($superficies['secondairesNoirs']['CARIGNAN N'])) {
            $superficies['secondairesNoirs']['TOTAL'] -= $superficies['secondairesNoirs']['CARIGNAN N'];
            $superficies['secondairesNoirs']['CARIGNAN N'] = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 10/80, $superficies['secondairesNoirs']['CARIGNAN N']);
            $superficies['secondairesNoirs']['TOTAL'] += $superficies['secondairesNoirs']['CARIGNAN N'];
        }
        // Affectation revendicables secondaires noirs
        $revendicableSecondairesBlancsMax = 0;
        if ($superficies['secondairesNoirs']['TOTAL'] <= $revendicableSecondairesMax) {
            $revendicables['secondairesNoirs'] = $superficies['secondairesNoirs']['TOTAL'];
            $revendicableSecondairesBlancsMax = round($revendicableSecondairesMax - $superficies['secondairesNoirs']['TOTAL'], 4);
        } else {
            $revendicables['secondairesNoirs'] = $revendicableSecondairesMax;
        }
        // Affectation revendicables secondaires blancs vermontino
        $revendicables['secondairesBlancsVermentino'] = $this->regleRevendicableAvecMax_GetRevendicable($revendicableSecondairesBlancsMax, $revendicableSecondairesBlancsVermontinoMax, $superficies['secondairesBlancsVermentino']['TOTAL']);
        $revendicableSecondairesBlancsMax = round($revendicableSecondairesBlancsMax - $revendicables['secondairesBlancsVermentino'], 4);
        // Affectation revendicables secondaires blancs autres
        $revendicables['secondairesBlancsAutres'] = $this->regleRevendicableAvecMax_GetRevendicable($revendicableSecondairesBlancsMax, $revendicableSecondairesBlancsAutresMax, $superficies['secondairesBlancsAutres']['TOTAL']);
        return $this->generateResultRevendicabe($revendicables, $superficies);
    }

    public function calculateRevendicableNDARose($superficies)
    {
        $revendicables = [];
        // Affectation renvendicables principaux
        $revendicables['principaux'] = $this->regleNbCepageMin_GetRevendicable($superficies['principaux'], 2);
        if ($revendicables['principaux'] > 0) {
            $revendicables['principaux'] = $this->reglePourcentageCepageMax_GetRevendicable($superficies['principaux'], $superficies['TOTAL'], 80);
        }
        // Affectation revendicables secondaires
        $revendicableSecondairesMax = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 20/80);
        $revendicableSecondairesBlancsVermontinoMax = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 20/80);
        $revendicableSecondairesBlancsAutresMax = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 10/80);
        // Affectation revendicables pour le cabernet sauvignon
        if (isset($superficies['secondairesNoirs']['CARIGNAN N'])) {
            $superficies['secondairesNoirs']['TOTAL'] -= $superficies['secondairesNoirs']['CARIGNAN N'];
            $superficies['secondairesNoirs']['CARIGNAN N'] = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 10/80, $superficies['secondairesNoirs']['CARIGNAN N']);
            $superficies['secondairesNoirs']['TOTAL'] += $superficies['secondairesNoirs']['CARIGNAN N'];
        }
        // Affectation revendicables secondaires noirs
        $revendicableSecondairesBlancsMax = 0;
        if ($superficies['secondairesNoirs']['TOTAL'] <= $revendicableSecondairesMax) {
            $revendicables['secondairesNoirs'] = $superficies['secondairesNoirs']['TOTAL'];
            $revendicableSecondairesBlancsMax = round($revendicableSecondairesMax - $superficies['secondairesNoirs']['TOTAL'], 4);
        } else {
            $revendicables['secondairesNoirs'] = $revendicableSecondairesMax;
        }
        // Affectation revendicables secondaires blancs vermontino
        $revendicables['secondairesBlancsVermentino'] = $this->regleRevendicableAvecMax_GetRevendicable($revendicableSecondairesBlancsMax, $revendicableSecondairesBlancsVermontinoMax, $superficies['secondairesBlancsVermentino']['TOTAL']);
        $revendicableSecondairesBlancsMax = round($revendicableSecondairesBlancsMax - $revendicables['secondairesBlancsVermentino'], 4);
        // Affectation revendicables secondaires blancs autres
        $revendicables['secondairesBlancsAutres'] = $this->regleRevendicableAvecMax_GetRevendicable($revendicableSecondairesBlancsMax, $revendicableSecondairesBlancsAutresMax, $superficies['secondairesBlancsAutres']['TOTAL']);
        return $this->generateResultRevendicabe($revendicables, $superficies);
    }
    
    public function calculateRevendicablePetiteSurface($superficies)
    {
        $revendicables = [];
        // Affectation renvendicables principaux
        $revendicables['principaux'] = $superficies['principaux']['TOTAL'];
        // Affectation revendicables secondaires
        $secondaires = round($superficies['secondairesNoirs']['TOTAL'] + $superficies['secondairesBlancsVermentino']['TOTAL'] + $superficies['secondairesBlancsAutres']['TOTAL'], 4);
        $revendicableSecondairesMax = $this->regleRatioMax_GetRevendicable($revendicables['principaux'], 50/100);
        if ($secondaires <= $revendicableSecondairesMax) {
            $revendicables['secondaires'] = $secondaires;
        } else {
            $revendicables['secondaires'] = $revendicableSecondairesMax;
        }
        return ['revendicables' => $revendicables, 'declassements' => ['principaux' => 0, 'secondaires' => round($secondaires - $revendicables['secondaires'], 4)]];
    }
    
    protected function generateResultRevendicabe($revendicables, $superficies)
    {
        $declassements = [];
        foreach (self::$categories as $cat) {
            if (isset($revendicables[$cat])) {
                $declassements[$cat] = round($superficies[$cat]['TOTAL'] - $revendicables[$cat], 4);
            }
        }
        return ['revendicables' => $revendicables, 'declassements' => $declassements];
    }

    protected function getDgcSuperficiesByCepage($dgc)
    {
        $aggCepage = [];
        foreach ($dgc as $couleur => $agg) {
            foreach ($agg as $k => $parcelles) {
                if ($k == 'TOTAL') continue;
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

    protected function respecteReglesEncepagement($parcelle)
    {
        $dgc = null;
        $currentCampagne = intval(ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_PREMIERE_ANNEE)->getCurrent() - 3);
        if (intval(substr($parcelle->campagne_plantation, 0, 4)) > $currentCampagne) {
            return false;
        }
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
    
    //******* REGLES CALCULS
    public function regleNbCepageMin_GetRevendicable($cepages, $min)
    {
        return (count($cepages) - 1 < $min)? 0 : $cepages['TOTAL'];
    }
    
    public function reglePourcentageCepageMax_GetRevendicable($cepages, $encepagement, $pourcentage)
    {
        $max = round(($encepagement*($pourcentage/100)), 4);
        $revendicable = 0;
        $total = 0;
        foreach ($cepages as $cepage => $superficie) {
            if ($cepage == 'TOTAL') {
                continue;
            }
            if ($superficie > $max) {
                $revendicable = round(($encepagement - $superficie) * ($pourcentage/(100-$pourcentage)), 4);
            } else {
                $total += $superficie;
            }
        }
        return ($revendicable)? round($revendicable + $total, 4) : $cepages['TOTAL'];
    }
    
    public function reglePourcentageCepageMin_GetEncepagementMax($cepagesKeys, $superficies, $pourcentage)
    {
        $max = 0;
        foreach ($cepagesKeys as $cepageKey) {
            foreach (self::$categories as $cat) {
                if (isset($superficies[$cat][$cepageKey])) {
                    $max += $superficies[$cat][$cepageKey];
                }
            }
        }
        $max = round($max * 1 / ($pourcentage / 100), 4);
        return ($max < $superficies['TOTAL'])? $max : $superficies['TOTAL'];
    }
    
    public function regleRatioMax_GetRevendicable($encepagementCepagesPrincipaux, $ratioSecondairePrincipal)
    {
        return round($encepagementCepagesPrincipaux*$ratioSecondairePrincipal, 4);
    }

    public function regleRevendicableAvecMax_GetRevendicable($revendicableGlobalMax, $revendicableExtraitMax, $superficie) 
    {
        $revendicable = 0;
        if ($revendicableExtraitMax > $revendicableGlobalMax) {
            $revendicableExtraitMax = $revendicableGlobalMax;
        }
        return ($superficie > $revendicableExtraitMax)? $revendicableExtraitMax : $superficie;
    }
    //****** FIN REGLES
}
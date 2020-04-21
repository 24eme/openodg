<?php
class PotentielProductionProvenceGenerator extends PotentielProductionGenerator
{
    protected $identificationParcellaire;
    
    protected $cepagesPrincipaux;
    protected $cepagesSecondairesNoirs;
    protected $cepagesSecondairesBlancs;
    
    public function __construct($identifiant_or_etablissement)
    {
        parent::__construct($identifiant_or_etablissement);
        $this->identificationParcellaire = ParcellaireAffectationClient::getInstance()->getLast($this->etablissement->identifiant);
        if (!$this->identificationParcellaire) {
            $this->identificationParcellaire = ParcellaireIntentionAffectationClient::getInstance()->getLast($this->etablissement->identifiant);
        }
        $this->cepagesPrincipaux = sfConfig::get('app_potentielproduction_cepagesprincipaux');
        $this->cepagesSecondairesNoirs = sfConfig::get('app_potentielproduction_cepagessecondairesnoirs');
        $this->cepagesSecondairesBlancs = sfConfig::get('app_potentielproduction_cepagessecondairesblancs');
        if (!$this->cepagesPrincipaux) {
            throw new sfException("potentielproduction_cepagesprincipaux non configuré dans l'app du projet");
        }
        if (!$this->cepagesSecondairesNoirs) {
            throw new sfException("potentielproduction_cepagessecondairesnoirs non configuré dans l'app du projet");
        }
        if (!$this->cepagesSecondairesBlancs) {
            throw new sfException("potentielproduction_cepagessecondairesblancs non configuré dans l'app du projet");
        }
    }
    public function infos() 
    {
        $infos = "** PotentielProductionProvenceGenerator **\n\n".parent::infos();
        return  ($this->identificationParcellaire)? $infos.' - Identification parcellaire : '.$this->identificationParcellaire->_id."\n" : $infos." - Identification parcellaire : null\n";
    }
    
    public function getSuperfices()
    {
        $parcelles = $this->parcellaire->getParcelles();
        $superficies = array('principaux' => array('TOTAL' => 0), 'secondairesnoirs' => array('TOTAL' => 0), 'secondairesblancs' => array('TOTAL' => 0), 'TOTAL' => 0);
        foreach ($parcelles as $parcelle) {
            if (!$this->respecteReglesEncepagement($parcelle)) {
                continue;
            }
            if (in_array($parcelle->cepage, $this->cepagesPrincipaux)) {
                if (!isset($superficies['principaux'][$parcelle->cepage])) {
                    $superficies['principaux'][$parcelle->cepage] = 0;
                }
                $superficies['principaux'][$parcelle->cepage] += ($parcelle->exist('superficie_affectation') && $parcelle->superficie_affectation)? $parcelle->superficie_affectation : $parcelle->superficie;
                $superficies['principaux']['TOTAL'] += ($parcelle->exist('superficie_affectation') && $parcelle->superficie_affectation)? $parcelle->superficie_affectation : $parcelle->superficie;
            } elseif (in_array($parcelle->cepage, $this->cepagesSecondairesNoirs)) {
                if (!isset($superficies['secondairesnoirs'][$parcelle->cepage])) {
                    $superficies['secondairesnoirs'][$parcelle->cepage] = 0;
                }
                $superficies['secondairesnoirs'][$parcelle->cepage] += ($parcelle->exist('superficie_affectation') && $parcelle->superficie_affectation)? $parcelle->superficie_affectation : $parcelle->superficie;
                $superficies['secondairesnoirs']['TOTAL'] += ($parcelle->exist('superficie_affectation') && $parcelle->superficie_affectation)? $parcelle->superficie_affectation : $parcelle->superficie;
            } elseif (in_array($parcelle->cepage, $this->cepagesSecondairesBlancs)) {
                if (!isset($superficies['secondairesblancs'][$parcelle->cepage])) {
                    $superficies['secondairesblancs'][$parcelle->cepage] = 0;
                }
                $superficies['secondairesblancs'][$parcelle->cepage] += ($parcelle->exist('superficie_affectation') && $parcelle->superficie_affectation)? $parcelle->superficie_affectation : $parcelle->superficie;
                $superficies['secondairesblancs']['TOTAL'] += ($parcelle->exist('superficie_affectation') && $parcelle->superficie_affectation)? $parcelle->superficie_affectation : $parcelle->superficie;
            }
            $superficies['TOTAL'] += ($parcelle->exist('superficie_affectation') && $parcelle->superficie_affectation)? $parcelle->superficie_affectation : $parcelle->superficie;
        }
        return $superficies;
    }
    
    public function respecteRegles()
    {
        $superficies = $this->getSuperfices();
        $regles = array();
        
        $percentCepagesPrincipaux = ($superficies['TOTAL'])? round(100 * ($superficies['principaux']['TOTAL'] / $superficies['TOTAL']), 2) : 0;
        $respect1 = ($percentCepagesPrincipaux >= 70)? 1 : 0;
        $regles["La proportion de l'ensemble des cépages principaux est supérieure ou égale à 70% de l'encépagement"] = $respect1;
        
        $nbCepagesPrincipaux = count($superficies['principaux']) - 1;
        $respect2 = ($nbCepagesPrincipaux >= 2)? 1 : 0;
        $regles["2 au moins des cépages principaux sont présents dans l'encépagement"] = $respect2;
        
        $superficieCepagesPrincipauxMax = round($superficies['TOTAL'] * 0.9, 4);
        $respect3 = 1;
        $cepagePrincipalError = null;
        foreach ($superficies['principaux'] as $cepage => $superficie) {
            if ($cepage == 'TOTAL') {
                continue;
            }
            if ($superficie > $superficieCepagesPrincipauxMax) {
                $respect3 = 0;
                $cepagePrincipalError = $cepage;
            }
        }
        $regles["la proportion de l'un des cépages principaux ne soit pas supérieure à 90% de l'encépagement"] = $respect3;
        
        $percentCepagesSecondairesBlancs = ($superficies['TOTAL'])? round(100 * ($superficies['secondairesblancs']['TOTAL'] / $superficies['TOTAL']), 2) : 0;
        $respect4 = ($percentCepagesSecondairesBlancs <= 20)? 1 : 0;
        $regles["La proportion de l'ensemble des cépages secondaires blancs est inférieure ou égale à 20% de l'encépagement"] = $respect4;
        
        $superficieSelectionBlancs = 0;
        foreach ($superficies['secondairesblancs'] as $cepage => $superficie) {
            if (in_array($cepage, array('TOTAL','VERMENTINO B'))) {
                continue;
            }
            $superficieSelectionBlancs += $superficie;
        }
        $percentSelectionBlancs = ($superficies['TOTAL'])? round(100 * ($superficieSelectionBlancs / $superficies['TOTAL']), 2) : 0;
        $respect5 = ($percentSelectionBlancs <= 10)? 1 : 0;
        $regles["La proportion de l'ensemble des cépages clairette B, sémillon B, ugni blanc B est inférieure ou égale à 10% de l'encépagement"] = $respect5;
        
        return $regles;
        
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
            if ($cdg) {
                return false;
            }
            if (!$parcelle->campagne_plantation || intval(substr($parcelle->campagne_plantation, -4)) > 1994) {
                return false;
            }
        }
        return true;
    }
}
<?php
/**
 * Model for ParcellaireAffectationProduitDetail
 *
 */

class ParcellaireAffectationProduitDetail extends BaseParcellaireAffectationProduitDetail {

    public function getProduit() {

        return $this->getParent()->getParent();
    }

    public function getProduitLibelle() {

        return $this->getProduit()->getLibelle();
    }
    
    public function getIdentificationParcelleLibelle() {
    	return $this->section.'-'.$this->numero_parcelle.'<br />'.$this->commune.' '.$this->getLieuLibelle().' '.sprintf("%0.2f&nbsp;<small class='text-muted'>ha</small>", $this->superficie);
    }
    
    public function getIdentificationCepageLibelle() {
    	return $this->getProduitLibelle().'<br />'.$this->getCepageLibelle().' '.$this->campagne_plantation;
    }
    
    public function getDgc() {
        $communesDenominations = sfConfig::get('app_communes_denominations');
        $dgcFinal = null;
        foreach ($communesDenominations as $dgc => $communes) {
            if (in_array($this->code_commune, $communes) && strpos($dgc, $this->getHash()) !== false) {
                return $dgc;
            }
            
            if (in_array($this->code_commune, $communes)) {
                $dgcFinal = $dgc;
            }
        }
        return $dgcFinal;
    }
    
    public function getDgcLibelle() {
        $dgc = $this->getDgc();
        
        if(!$dgc) {
            
            return null;
        }
        
        return $this->getDocument()->getDgcLibelle($dgc);
    }

    public function getLieuLibelle() {
        if ($this->lieu) {

            return $this->lieu;
        }

        return $this->getLieuNode()->getLibelle();
    }
    
    public function getCepageLibelle() {

        return $this->getCepage();
    }

    public function getLieuNode() {

        return $this->getProduit()->getConfig()->getLieu();
    }

    public function getDateAffectationFr() {
        if (!$this->date_affectation) {
            return null;
        }
        $date = new DateTime($this->date_affectation);
    
        return $date->format('d/m/Y');
    }
}

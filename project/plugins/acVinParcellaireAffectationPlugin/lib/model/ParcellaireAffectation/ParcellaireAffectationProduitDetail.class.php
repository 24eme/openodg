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

    public function getProduitHash() {
        if ($this->_get('produit_hash')) {
            return $this->_get('produit_hash');
        }
        return $this->getParent()->getParent()->getHash();
    }

    public function getDgc() {
        $communesDenominations = sfConfig::get('app_communes_denominations');
        $dgcFinal = null;
        foreach ($communesDenominations as $dgc => $communes) {
            if (!in_array($this->code_commune, $communes)) {
                continue;
            }
            if (strpos($dgc, $this->getLieuNode()->getKey()) !== false) {
                
                return $dgc;
            }
            
            $dgcFinal = $dgc;
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

    public function getSuperficie() {
        if ($this->exist('superficie_affectation') && $this->_get('superficie_affectation')) {
            return $this->_get('superficie_affectation');
        }
        if ($this->_get('superficie')) {
            return $this->_get('superficie');
        }
        return $this->getSuperficieParcellaire();
    }

    public function getSuperficieParcellaire() {
        $p = $this->getDocument()->getParcelleFromParcellaire($this->getParcelleId());
        if (!$p) {
            return $this->superficie;
        }
        return $p->superficie;
    }

    public function isPartielle() {
        return round($this->superficie,4) != round($this->getSuperficieParcellaire(),4);
    }
}

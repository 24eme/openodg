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

    public function getSuperficie($identifiant = null) {
        if($identifiant && $this->exist('destinations/'.$identifiant)) {

            return $this->get('destinations/'.$identifiant.'/superficie');
        }

        if ($this->exist('superficie_affectation') && $this->_get('superficie_affectation')) {
            return $this->_get('superficie_affectation');
        }
        if ($this->_get('superficie') != null) {
            return $this->_get('superficie');
        }
        return $this->getSuperficieParcellaire();
    }

    public function getSuperficieParcellaire() {
        $p = $this->getDocument()->getParcelleFromParcellaire($this->getParcelleId());
        if (!$p) {
            if (!$this->_get('superficie_cadastrale')) {
                $this->_set('superficie_cadastrale', $this->superficie);
            }
        } else {
            if ($this->_get('superficie_cadastrale') != $p->superficie) {
                $this->_set('superficie_cadastrale', $p->superficie);
            }
        }
        return $this->_get('superficie_cadastrale');
    }

    public function isPartielle() {
        return round($this->superficie,4) != round($this->getSuperficieParcellaire(),4);
    }

    public function updateAffectations() {
        if(!$this->exist('destinations')) {
            return;
        }

        $this->superficie = 0;
        foreach($this->destinations as $destination) {
            $this->superficie = $this->_get('superficie') + $destination->superficie;
        }

        $this->affectee = intval(boolval($this->superficie));
    }

    public function affecter($superficie, Etablissement $etablissement) {
        $this->affectee = 1;
        $destination = $this->add('destinations')->add($etablissement->identifiant);
        $destination->identifiant = $etablissement->identifiant;
        $destination->cvi = $etablissement->cvi;
        $destination->superficie = $superficie;
        $this->updateAffectations();
    }
}

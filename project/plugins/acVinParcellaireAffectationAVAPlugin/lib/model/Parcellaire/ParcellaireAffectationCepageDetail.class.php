<?php

/**
 * Model for ParcellaireCepageDetail
 *
 */
class ParcellaireAffectationCepageDetail extends BaseParcellaireAffectationCepageDetail {

    public function getGenre() {
        return $this->getParent();
    }

    public function getProduitHash() {
        return $this->getParent()->getParent()->getHash();
    }

    public function getChildrenNode() {
        return $this->getMentions();
    }

    public function getMentions() {
        return $this->filter('^mention');
    }

    public function getAcheteursCepage() {

        return $this->getCepage()->getAcheteursNode($this->lieu);
    }

    public function getSuperficieParcellaire() {
        if (!$this->_get('superficie_parcellaire')) {
            $this->_set('superficie_parcellaire', $this->_get('superficie'));
        }
        return $this->_get('superficie_parcellaire');
    }

    public function getSuperficie($unite = ParcellaireClient::PARCELLAIRE_SUPERFICIE_UNIT_HECTARE) {
        if($unite == ParcellaireClient::PARCELLAIRE_SUPERFICIE_UNIT_ARE) {
            return round($this->_get('superficie') * 100, 2);
        }

        return $this->_get('superficie');
    }

    public function getAcheteursCepageByCVI() {
        $acheteursCvi = array();
        foreach($this->getAcheteursCepage() as $type => $acheteurs) {
            foreach($acheteurs as $cvi => $acheteur) {
                $acheteursCvi[$cvi] = $acheteur;
            }
        }

        return $acheteursCvi;
    }

    public function getAcheteursByCVI() {
        if(!$this->exist('acheteurs')) {
            return $this->getAcheteursCepageByCVI();
        }
        $acheteursCvi = array();
        foreach($this->getAcheteursCepageByCVI() as $cvi => $acheteur) {
            if(!in_array($cvi, $this->acheteurs->toArray(true, false))) {

                continue;
            }

            $acheteursCvi[$cvi] = $acheteur;
        }

        return $acheteursCvi;
    }

    public function hasMultipleAcheteur() {
        $nbParcelle = 0;
        foreach($this->getCepage()->getProduitsCepageDetails() as $p) {
            if($this->lieu && $this->lieu != $p->lieu) {
                continue;
            }
            $nbParcelle++;
        }

        if($nbParcelle <= 1) {

            return false;
        }

        $acheteurs = $this->getAcheteursCepageByCVI();

        if(count($acheteurs) <= 1) {
            return false;
        }

        return true;
    }

    public function getProduitsCepageDetails($onlyVtSgn = false, $active = false) {
		if ($active && !$this->getActive()) {
			return array();
		}
        return array($this->getHash() => $this);
    }

    public function getLibelleComplet() {
        return $this->getCepage()->getLibelleComplet();
    }

    public function getLieuLibelle() {
        if ($this->lieu) {

            return $this->lieu;
        }

        return ($this->getLieuNode()->getLibelle()) ? $this->getLieuNode()->getLibelle() : null;
    }

    public function getParcelleIdentifiant() {
        return sprintf('%s %03s %03s', $this->commune, $this->section, $this->numero_parcelle);
    }

    public function getAppellation() {
        return $this->getCepage()->getAppellation();
    }

    public function getCepage() {

        return $this->getParent()->getParent();
    }

    public function getCepageLibelle() {

        return $this->getCepage()->getLibelle();
    }

    public function getCouleur() {

        return $this->getCepage()->getCouleur();
    }

    public function isCleanable() {
    	if (!$this->getActive()) {
    		return true;
    	}
        return ($this->isFromAppellation('ALSACEBLANC') && !$this->getVtsgn());
    }

    public function isAffectee($lieu = null) {
        if ($lieu && $this->lieu && KeyInflector::slugify(trim($lieu)) != KeyInflector::slugify(trim($this->lieu))) {

    		return false;
    	}

        return !$this->isCleanable();
    }

    public function getLieuNode() {

        return $this->getCouleur()->getLieu();
    }

    public function getLieu() {
        if (!$this->getAppellation()->getConfig()->hasLieuEditable()) {
            return null;
        }
        return $this->_get('lieu');
    }

    public function getLieuDitCadastral() {
        if($this->exist('lieu_cadastral')) {

            return $this->_get('lieu_cadastral');
        }
        if(!$this->getParcelleParcellaire()) {
            return null;
        }
        return $this->getParcelleParcellaire()->getLieu();
    }

    public function cleanNode() {
        if(!$this->hasMultipleAcheteur()) {
            $this->remove('acheteurs');
        }
        if (!$this->getAppellation()->getConfig()->hasLieuEditable()) {
            $this->lieu = '';
        }
        return false;
    }

    public function getActive() {
        $v = $this->_get('active');
        if (!$this->superficie) {
            return false;
        }
        if ($this->getDocument()->isParcellaireCremant() && !$this->isFromAppellation('CREMANT')) {
          return false;
        }
        if ($v === null) {
            return true;
        }
        return ($v) ? true : false;
    }
    public function setActive($value) {
        return $this->_set('active', $value * 1);
    }
    public function getVtsgn() {
        $v = $this->_get('vtsgn');
        if ($v === null || !$this->superficie) {
            return false;
        }
        return ($v) ? true : false;
    }
    public function setVtsgn($value) {
        return $this->_set('vtsgn', $value * 1);
    }

    public function isFromAppellation($appellation){
        return 'appellation_'.$appellation == $this->getAppellation()->getKey();
    }

    public function getAppellationLibelle($force_vtsgn = false) {
        $l = $this->getAppellation()->getLibelle();
        if ($this->getLieuNode()->getLibelle()) {
            $l .= ' '.$this->getLieuNode()->getLibelle();
        }
        if ($force_vtsgn || $this->getVtsgn()) {
            $l .= ' VT/SGN';
        }
        return  $l;
    }

    public function getParcelleParcellaire() {
        $p = $this->getDocument()->getParcellaire()->getDeclarationParcelles();
        if (!isset($p[$this->getParcelleId()])) {
            return null;
        }
        return $p[$this->getParcelleId()];
    }

    public function isRealParcelleIdFromParcellaire() {
        return $this->_get('parcelle_id') && strpos($this->_get('parcelle_id'), '-X') === false;
    }

}

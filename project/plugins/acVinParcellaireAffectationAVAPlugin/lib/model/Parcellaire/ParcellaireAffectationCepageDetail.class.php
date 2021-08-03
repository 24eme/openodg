<?php

/**
 * Model for ParcellaireCepageDetail
 *
 */
class ParcellaireAffectationCepageDetail extends BaseParcellaireAffectationCepageDetail {

    public function getGenre() {
        return $this->getParent();
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
        foreach($acheteurs as $acheteur) {
            if($acheteur->cvi == $this->getDocument()->identifiant) {
                unset($acheteurs[$acheteur->cvi]);
            }

        }

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
        return $this->getAppellation()->getLibelleComplet().' '.$this->getLieuLibelle().' '.$this->getCepageLibelle();
    }

    public function getLieuLibelle() {
        if ($this->lieu) {

            return $this->lieu;
        }

        return $this->getLieuNode()->getLibelle();
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

    public function cleanNode() {
        if(!$this->hasMultipleAcheteur()) {
            $this->remove('acheteurs');
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
}

<?php

class DRevCouleur extends BaseDRevCouleur
{

	public function getChildrenNode()
    {
        return $this->getCepages();
    }

    public function getCepages() {

        return $this->filter('^cepage_');
    }

	public function getLieu()
    {
        return $this->getParent();
    }

	public function getMention()
    {
        return $this->getLieu()->getMention();
    }

    public function getAppellation()
    {
    	return $this->getMention()->getAppellation();
    }

    public function getProduits($onlyActive = false)
    {
    	if ($onlyActive && !$this->isActive()) {

    		return array();
    	}

        return array($this->getHash() => $this);
    }

    public function getProduitHash() {
        if(!$this->getMention()->getConfig()->hasManyNoeuds()) {

            return $this->getHash();
        }


        return $this->getMention()->getHash()."/lieu/".$this->getKey();
    }

    public function getProduitsCepage() {

        if(!$this->getMention()->hasManyNoeuds()) {

            return parent::getProduitsCepage();
        }

        if($this->getLieu()->getKey() != "lieu") {

            return parent::getProduitsCepage();
        }

        $produits = array();
        foreach($this->getMention()->getChildrenNode() as $lieu) {
            if(!$lieu->exist($this->getKey())) {
                continue;
            }

            foreach($lieu->get($this->getKey())->getChildrenNode() as $cepage) {
                $produits = array_merge($produits, $cepage->getProduitsCepage());
            }
        }

        return $produits;
    }

    public function updateFromCepage() {
        $this->volume_revendique = 0;
        foreach($this->getAppellation()->getProduitsCepage() as $produit) {
            if($produit->getCouleur()->getKey() != $this->getKey()) {
                continue;
            }

            $produit->updateTotal();

            $this->volume_revendique += $produit->volume_revendique_total;
        }
    }

    public function getTotalTotalSuperficie()
    {
    	return ($this->isActive())? $this->superficie_revendique : 0;
    }

    public function getTotalVolumeRevendique()
    {
    	return ($this->isActive())? $this->volume_revendique : 0;
    }

    public function resetDetail() {
        $this->remove('detail');
		$this->remove('detail_vtsgn');
        $this->add('detail');
        $this->add('detail_vtsgn');
    }

    public function updateDetail() {
        if($this->detail->usages_industriels_sur_place === -1) {
           $this->detail->volume_sur_place_revendique = null;
           $this->detail->usages_industriels_sur_place = null;
        }

		if($this->detail_vtsgn->usages_industriels_sur_place === -1) {
           $this->detail_vtsgn->volume_sur_place_revendique = null;
           $this->detail_vtsgn->usages_industriels_sur_place = null;
        }

        if(!is_null($this->detail->volume_sur_place) && !is_null($this->detail->usages_industriels_sur_place)) {
            $this->detail->volume_sur_place_revendique = $this->detail->volume_sur_place - $this->detail->usages_industriels_sur_place;
        }

		if(!is_null($this->detail_vtsgn->volume_sur_place) && !is_null($this->detail_vtsgn->usages_industriels_sur_place)) {
            $this->detail_vtsgn->volume_sur_place_revendique = $this->detail_vtsgn->volume_sur_place - $this->detail_vtsgn->usages_industriels_sur_place;
        }
    }

    public function updateRevendiqueFromDetail() {
        if(!is_null($this->detail->superficie_total)) {
            $this->superficie_revendique = $this->detail->superficie_total;
        }

		if(!is_null($this->detail_vtsgn->superficie_total)) {
			$this->superficie_revendique_vtsgn = $this->detail_vtsgn->superficie_total;
		}

        if(!is_null($this->detail->volume_sur_place_revendique)) {
            $this->volume_revendique = $this->detail->volume_sur_place_revendique;
        }

		if(!is_null($this->detail_vtsgn->volume_sur_place_revendique)) {
			$this->volume_revendique_vtsgn = $this->detail_vtsgn->volume_sur_place_revendique;
		}
    }

    public function isProduit() {

        return $this->getProduitHash() == $this->getHash();
    }

    public function isActive()
    {

	    return ($this->volume_revendique > 0 || $this->superficie_revendique > 0);
    }

    public function isCleanable() {

        if(!$this->isProduit()) {

            return parent::isCleanable();
        }

        if(!$this->volume_revendique && !$this->superficie_revendique && !count($this->getProduitsCepage())) {

            return true;
        }

        return false;
    }


}

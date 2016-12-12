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
		if($this->exist('volume_revendique_vtsgn')) {
			$this->volume_revendique_vtsgn = 0;
		}
        foreach($this->getAppellation()->getProduitsCepage() as $produit) {
            if($produit->getCouleur()->getKey() != $this->getKey()) {
                continue;
            }

            $produit->updateTotal();

			if($this->canHaveVtsgn()) {
				$this->volume_revendique_vtsgn += $produit->volume_revendique_vt + $produit->volume_revendique_sgn;
			}

			$this->volume_revendique += $produit->volume_revendique;
        }
    }

    public function getTotalTotalSuperficie()
    {

		return $this->superficie_revendique + (($this->canHaveVtsgn()) ? $this->superficie_revendique_vtsgn : 0);
    }

    public function getTotalVolumeRevendique()
    {

    	return $this->volume_revendique + (($this->canHaveVtsgn()) ? $this->volume_revendique_vtsgn : 0);
    }

    public function resetDetail() {
        $this->remove('detail');
        $this->add('detail');

		if($this->canHaveVtsgn()) {
			$this->remove('detail_vtsgn');
			$this->add('detail_vtsgn');
		}
    }

    public function updateDetail() {
        if($this->detail->usages_industriels_sur_place === -1) {
           $this->detail->volume_sur_place_revendique = null;
           $this->detail->usages_industriels_sur_place = null;
        }

		if($this->canHaveVtsgn()) {
			if($this->detail_vtsgn->usages_industriels_sur_place === -1) {
           		$this->detail_vtsgn->volume_sur_place_revendique = null;
           		$this->detail_vtsgn->usages_industriels_sur_place = null;
        	}
		}

        if(!is_null($this->detail->volume_sur_place) && !is_null($this->detail->usages_industriels_sur_place)) {
            $this->detail->volume_sur_place_revendique = $this->detail->volume_sur_place - $this->detail->usages_industriels_sur_place;
        }

		if($this->canHaveVtsgn()) {
			if(!is_null($this->detail_vtsgn->volume_sur_place) && !is_null($this->detail_vtsgn->usages_industriels_sur_place)) {
            	$this->detail_vtsgn->volume_sur_place_revendique = $this->detail_vtsgn->volume_sur_place - $this->detail_vtsgn->usages_industriels_sur_place;
        	}
		}
    }

    public function updateRevendiqueFromDetail() {
        if(!is_null($this->detail->superficie_total)) {
            $this->superficie_revendique = $this->detail->superficie_total;
        }

		if($this->canHaveVtsgn()) {
			if(!is_null($this->detail_vtsgn->superficie_total)) {
				$this->superficie_revendique_vtsgn = $this->detail_vtsgn->superficie_total;
			}
		}

        if(!is_null($this->detail->volume_sur_place_revendique)) {
            $this->volume_revendique = $this->detail->volume_sur_place_revendique;
        }

		if($this->canHaveVtsgn()) {
			if(!is_null($this->detail_vtsgn->volume_sur_place_revendique)) {
				$this->volume_revendique_vtsgn = $this->detail_vtsgn->volume_sur_place_revendique;
			}
		}
    }

	public function canHaveVtsgn() {

		return $this->exist('detail_vtsgn');
	}


    public function isProduit() {

        return $this->getProduitHash() == $this->getHash();
    }

    public function isActive()
    {

	    return ($this->getTotalVolumeRevendique() > 0 || $this->getTotalTotalSuperficie() > 0);
    }

    public function isCleanable() {

        if(!$this->isProduit()) {

            return parent::isCleanable();
        }

        if(!$this->getTotalVolumeRevendique() && !$this->getTotalTotalSuperficie() && !count($this->getProduitsCepage())) {

            return true;
        }

        return false;
    }


}

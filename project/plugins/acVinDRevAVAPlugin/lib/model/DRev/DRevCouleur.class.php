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

    public function getCouleur() {

        return $this;
    }

    public function getProduitsVCI()
    {
        $produits = array();
        foreach($this->getCepages() as $key => $cepage) {
        	if ($cepage->exist('detail')) {
	        	foreach ($cepage->detail as $key => $item) {
	            	$produits = array_merge($produits, $item->getProduitsVCI());
	        	}
        	}
        }
    	if ($this->exist('vci')) {
    		foreach ($this->vci as $subkey => $subitem) {
    			$produits = array_merge($produits, array($subitem->getHash() => $subitem));
    		}
    	}
        return $produits;
    }
    
    public function getTotalConstitue() {
    	$val = 0;
    	foreach ($this->getProduitsVCI() as $p) {
    		$val += $p->constitue;
    	}
    	return $val;
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

	public function getVolumeRevendiqueRecolte() {
		if(!$this->exist('volume_revendique_recolte')) {

			return $this->volume_revendique;
		}

		return $this->_get('volume_revendique_recolte');
	}

    public function updateFromCepage() {
		$this->add('volume_revendique_recolte');
        $this->volume_revendique_recolte = 0;
		if($this->exist('volume_revendique_vtsgn')) {
			$this->volume_revendique_vtsgn = 0;
		}
		if($this->canHaveSuperficieVinifiee()) {
        	$this->superficie_vinifiee = 0;
			if($this->exist('superficie_vinifiee_vtsgn')) {
				$this->superficie_vinifiee_vtsgn = 0;;
			}
		}
        foreach($this->getAppellation()->getProduitsCepage() as $produit) {
            if($produit->getCouleur()->getKey() != $this->getKey()) {
                continue;
            }

            $produit->updateTotal();

			$this->volume_revendique_recolte += $produit->volume_revendique_recolte;

			if($this->canHaveVtsgn()) {
				$this->volume_revendique_vtsgn += $produit->volume_revendique_vt + $produit->volume_revendique_sgn;
			}

			if($produit->canHaveSuperficieVinifiee()) {
				$this->superficie_vinifiee += $produit->superficie_vinifiee;

				if($this->canHaveVtsgn()) {
					$this->superficie_vinifiee_vtsgn += $produit->superficie_vinifiee_vt + $produit->superficie_vinifiee_sgn;
				}
			}
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

	public function getTotalVolumeRevendiqueVCI()
	{

		return $this->exist('volume_revendique_vci') ? $this->volume_revendique_vci : 0;
	}

    public function getTotalSuperficieVinifiee()
    {
    	if (!$this->exist('superficie_vinifiee')) {
    		return 0;
    	}
    	$superficie = $this->superficie_vinifiee + (($this->canHaveVtsgn() && $this->exist('superficie_vinifiee_vtsgn')) ? $this->superficie_vinifiee_vtsgn : 0);
    	// Conversion de la superficie en hectares pour la facturation
    	return ($superficie > 0)? round($superficie / 100,4) : 0;
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
            $this->detail->volume_sur_place_revendique = $this->detail->volume_sur_place - $this->detail->usages_industriels_sur_place - $this->detail->vci_sur_place;
        }

		if($this->canHaveVtsgn()) {
			if(!is_null($this->detail_vtsgn->volume_sur_place) && !is_null($this->detail_vtsgn->usages_industriels_sur_place)) {
				$this->detail_vtsgn->volume_sur_place_revendique = $this->detail_vtsgn->volume_sur_place - $this->detail_vtsgn->usages_industriels_sur_place - $this->detail_vtsgn->vci_sur_place;
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
            $this->add('volume_revendique_recolte', $this->detail->volume_sur_place_revendique);
        }

		if($this->canHaveVtsgn()) {
			if(!is_null($this->detail_vtsgn->volume_sur_place_revendique)) {
				$this->volume_revendique_vtsgn = $this->detail_vtsgn->volume_sur_place_revendique;
			}
		}

        if($this->detail->exist('superficie_vinifiee') && !is_null($this->detail->superficie_vinifiee)) {
        	$this->add('superficie_vinifiee', $this->detail->superficie_vinifiee);
        }

		if($this->canHaveVtsgn()) {
			if($this->detail_vtsgn->exist('superficie_vinifiee') && !is_null($this->detail_vtsgn->superficie_vinifiee)) {
	        	$this->add('superficie_vinifiee_vtsgn', $this->detail_vtsgn->superficie_vinifiee);
			}
		}
    }

	public function canHaveVtsgn() {

		return $this->exist('detail_vtsgn');
	}

	public function canHaveSuperficieVinifiee() {

		return $this->exist('superficie_vinifiee') || $this->exist('superficie_vinifiee_vtsgn') ;
	}

	public function hasVciRecolteConstitue() {

		return $this->detail->vci_total || ($this->exist('detail_vtsgn') && $this->detail_vtsgn->vci_total > 0);
	}

	public function hasVolumeRevendiqueVci() {
		return ($this->exist('volume_revendique_vci') && $this->volume_revendique_vci > 0);
	}

    public function isProduit() {

        return $this->getProduitHash() == $this->getHash();
    }

    public function isActive()
    {

	    return ($this->getTotalVolumeRevendique() > 0 || $this->getTotalTotalSuperficie() > 0 || $this->getTotalSuperficieVinifiee() > 0);
    }

    public function isCleanable() {

        if(!$this->isProduit()) {

            return parent::isCleanable();
        }

        if(!$this->getTotalVolumeRevendique() && !$this->getTotalTotalSuperficie() && !$this->getTotalSuperficieVinifiee() && !count($this->getProduitsCepage()) && !$this->exist('vci')) {

            return true;
        }

        return false;
    }

		public function setVolumeRevendiqueVCI($v, $vciStockageNegoce = 0) {
			parent::_set('volume_revendique_vci', $v);

			$v = $v - $vciStockageNegoce;
			
			if (!$this->exist('volume_revendique_recolte')){
				return $this->setVolumeRevendique($v);
			}

			return $this->setVolumeRevendique( $v + $this->get('volume_revendique_recolte'));
		}

		public function setVolumeRevendiqueRecolte($v) {
			parent::_set('volume_revendique_recolte', $v);
			if (!$this->exist('volume_revendique_vci')){
				return $this->setVolumeRevendique( $v);
			}
			$this->setVolumeRevendique( $v + $this->get('volume_revendique_vci'));
		}

}

<?php

class DRevProduit extends BaseDRevProduit
{
	public function getConfig()
	{
		return $this->getCouchdbDocument()->getConfiguration()->get($this->getHash());
	}

	public function getLibelle() {
		if(!$this->_get('libelle')) {
			$this->libelle = $this->getConfig()->getLibelleComplet();
		}

		return $this->_get('libelle');
	}

	public function getLibelleComplet()
	{

		return $this->getLibelle();
	}

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

    public function getProduitHash() {

		return $this->getHash();
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

			$this->volume_revendique += $produit->volume_revendique;
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

		return $this->volume_revendique_total + (($this->canHaveVtsgn()) ? $this->volume_revendique_vtsgn : 0);
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

        if($this->detail->exist('superficie_vinifiee') && !is_null($this->detail->superficie_vinifiee)) {
        	$this->add('superficie_vinifiee', $this->detail->superficie_vinifiee);
        }

		if($this->canHaveVtsgn()) {
			if($this->detail_vtsgn->exist('superficie_vinifiee') && !is_null($this->detail_vtsgn->superficie_vinifiee)) {
	        	$this->add('superficie_vinifiee_vtsgn', $this->detail_vtsgn->superficie_vinifiee);
			}
		}
    }

	public function getTotalVciUtilise() {

		return $this->vci->complement + $this->vci->substitution + $this->vci->rafraichi + $this->vci->destruction;
	}

	public function getPlafondStockVci() {

		return $this->superficie_revendique * $this->getConfig()->rendement_vci_total;
	}

	public function canHaveVtsgn() {

		return $this->exist('detail_vtsgn');
	}

	public function canHaveSuperficieVinifiee() {

		return $this->exist('superficie_vinifiee');
	}

	public function hasVci($saisie = false) {
		if ($saisie) {
			return ($this->vci->stock_precedent || $this->vci->destruction || $this->vci->complement || $this->vci->substitution || $this->vci->rafraichi || $this->vci->constitue);
		}
		return ($this->vci->stock_precedent !== null || $this->vci->destruction !== null || $this->vci->complement !== null || $this->vci->substitution !== null || $this->vci->rafraichi !== null || $this->vci->constitue !== null);
	}

	public function hasVciDetruit() {
		return ($this->vci->destruction && $this->vci->destruction > 0)? true : false;
	}

    public function isActive()
    {
		return true;
	    return ($this->getTotalVolumeRevendique() > 0 || $this->getTotalTotalSuperficie() > 0 || $this->getTotalSuperficieVinifiee() > 0);
    }

    public function isCleanable() {

        if(!$this->isActive()) {

            return true;
        }

        return false;
    }

	public function update($params = array()) {
		$this->vci->stock_final = null;
		$this->volume_revendique_issu_vci = null;
		if($this->hasVci()) {
			$this->volume_revendique_issu_vci = ((float) $this->vci->complement) + ((float) $this->vci->substitution) + ((float) $this->vci->rafraichi);
			$this->vci->stock_final = ((float) $this->vci->rafraichi) + ((float) $this->vci->constitue);
		}
		$this->volume_revendique_total = ((float) $this->volume_revendique_issu_recolte) + ((float) $this->volume_revendique_issu_vci);
	}


}

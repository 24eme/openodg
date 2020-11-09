<?php

class DRevDeclaration extends BaseDRevDeclaration
{

	public function getChildrenNode()
    {
        return $this->getCertifications();
    }

    public function getCertifications()
    {
        return $this->filter('^certification');
    }

    public function getAppellations()
    {
        if(!$this->exist('certification')) {
        	return array();
        }
        return $this->getChildrenNodeDeep(2)->getAppellations();
    }

	public function hasVolumeRevendiqueInCepage() {
		foreach ($this->getProduitsCepage() as $produit) {
			if($produit->volume_revendique_total > 0) {

				return true;
			}
		}
	}

	public function hasVciRecolteConstitue() {
		foreach($this->getProduits() as $produit) {
			if($produit->hasVciRecolteConstitue()) {

				return true;
			}
		}

		return false;
	}

	public function hasVolumeRevendiqueVci() {
		foreach($this->getProduits() as $produit) {
			if($produit->hasVolumeRevendiqueVci()) {
				return true;
			}
		}
		return false;
	}

}

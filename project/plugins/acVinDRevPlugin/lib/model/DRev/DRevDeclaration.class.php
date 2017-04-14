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

}

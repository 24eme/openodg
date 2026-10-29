<?php
/**
 * Model for ParcellaireLieu
 *
 */

class ParcellaireAffectationLieu extends BaseParcellaireAffectationLieu {
	public function getMention()
    {
        return $this->getParent();
    }

    public function getAppellation() 
    {
        return $this->getMention()->getParent();
    }

    public function getChildrenNode() 
    {
        return $this->getCouleurs();
    }

    public function getProduits($onlyActive = false) 
    {

        return parent::getProduits($onlyActive);
    }

    public function getCouleurs() 
    {
        return $this->filter('^couleur');
    }
}
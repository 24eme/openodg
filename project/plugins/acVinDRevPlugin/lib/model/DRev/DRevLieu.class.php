<?php

class DRevLieu extends BaseDRevLieu 
{


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
        if($this->getKey() != "lieu") {

            return array();
        }

        return parent::getProduits($onlyActive);
    }

    public function getCouleurs() 
    {
        return $this->filter('^couleur');
    }

}

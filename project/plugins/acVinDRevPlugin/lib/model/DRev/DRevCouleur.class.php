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
    
    public function getTotalTotalSuperficie()
    {
    	return ($this->isActive())? $this->total_superficie : 0;
    }
    
    public function getTotalVolumeRevendique()
    {
    	return ($this->isActive())? $this->volume_revendique : 0;
    }

    public function updateFromDR() {
        if($this->volume_sur_place_revendique === -1) {
           $this->volume_sur_place_revendique = null; 
        }
        if(!is_null($this->superficie_total)) {
            $this->superficie_revendique = $this->superficie_total;
        }

        if(!is_null($this->volume_sur_place_revendique)) {
            $this->volume_revendique = $this->volume_sur_place_revendique;
        }
    }
    
    public function isActive()
    {
	    return ($this->volume_revendique !== null && $this->superficie_revendique !== null)? true : false;
    }
    
}

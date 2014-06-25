<?php

class DRevCouleur extends BaseDRevCouleur 
{

	public function getChildrenNode() 
    {
        return null;
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
        if($this->dr->volume_sur_place_revendique === -1) {
           $this->dr->volume_sur_place_revendique = null; 
        }
        if(!is_null($this->dr->superficie_total)) {
            $this->total_superficie = $this->dr->superficie_total;
        }

        if(!is_null($this->dr->volume_sur_place_revendique)) {
            $this->volume_revendique = $this->dr->volume_sur_place_revendique;
        }
    }
    
    public function isActive()
    {
	    return ($this->volume_revendique !== null && $this->total_superficie !== null)? true : false;
    }
    
}

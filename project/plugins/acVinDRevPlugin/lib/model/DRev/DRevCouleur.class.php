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
    	if ($onlyActive && !$this->actif) {
    		return array();
    	}
        return array($this->getHash() => $this);
    }
    
    
    public function getTotalTotalSuperficie()
    {
    	return ($this->total_superficie && $this->actif)? $this->total_superficie : 0;
    }
    
    public function getTotalVolumeRevendique()
    {
    	return ($this->volume_revendique && $this->actif)? $this->volume_revendique : 0;
    }
    
    public function defineActive()
    {
    	$this->actif = 0;
	    if ($this->volume_revendique && $this->total_superficie) {
	    	$this->actif = 1;
	    }
    }
    
    public function clear()
    {
    	$this->actif = 0;
    	$this->volume_revendique = null;
    	$this->total_superficie = null;
    }
    
}

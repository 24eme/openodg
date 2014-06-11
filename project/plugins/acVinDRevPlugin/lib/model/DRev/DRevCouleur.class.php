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

    public function getProduits() 
    {
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

<?php
/**
 * Model for DRevLotCepage
 *
 */

class DRevLotCepage extends BaseDRevLotCepage
{

    public function hasVtsgn()
    {
    	return ($this->nb_vtsgn)? true : false;
    }
    
	public function hasHorsVtsgn()
    {
    	return ($this->nb_hors_vtsgn)? true : false;
    }
    
    public function hasLots($vtsgn = false, $horsvtsgn = false)
    {
    	if ($vtsgn != $horsvtsgn) {
	    	if ($vtsgn) {
	    		return $this->hasVtsgn();
	    	}
	    	if ($horsvtsgn) {
	    		return $this->hasHorsVtsgn();
	    	}
    	}
    	return ($this->hasVtsgn() || $this->hasHorsVtsgn())? true : false;
    }
    
    public function hasConfigVtsgn() {

        return !$this->exist('no_vtsgn') || !$this->get('no_vtsgn');

    }

}
<?php
/**
 * Model for DRevLotCepage
 *
 */

class DRevLotCepage extends BaseDRevLotCepage
{
    public function getConfig() {

        return $this->getDocument()->getConfiguration()->get($this->hash_produit);
    }

    public function getLibelle() {
        if(is_null($this->_get('libelle'))) {
            $libelle = '';
            if ($this->getConfig()->getLieu()->libelle) {
                $libelle .= $this->getConfig()->getLieu()->libelle.' - ';
            }
            $libelle .= $this->getConfig()->libelle;
            $this->_set('libelle', $libelle);
        }

        return $this->_get('libelle');
    }

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
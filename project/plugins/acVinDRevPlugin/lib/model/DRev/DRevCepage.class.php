<?php
/**
 * Model for DRevCepage
 *
 */

class DRevCepage extends BaseDRevCepage {
    
    public function getChildrenNode() 
    {
        return null;
    }

    public function resetRevendique() {
        $this->superficie_revendique = null;
        $this->volume_revendique = null;
        $this->volume_revendique_vtsgn = null;
    }

    public function getProduitsCepage() 
    {

        return array($this->getHash() => $this);
    }
}
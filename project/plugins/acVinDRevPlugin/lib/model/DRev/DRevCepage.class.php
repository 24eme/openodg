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

    public function reorderByConf() {

        return null;
    }

    public function resetRevendique() {
        $this->superficie_revendique = null;
        $this->volume_revendique = null;
        $this->volume_revendique_vt = null;
        $this->volume_revendique_sgn = null;
    }

    public function hasVtsgn() {

        return $this->volume_revendique_vt || $this->volume_revendique_sgn;
    }

    public function getProduitsCepage() 
    {

        return array($this->getHash() => $this);
    }
    }

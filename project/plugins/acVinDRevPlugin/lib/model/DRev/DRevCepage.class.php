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

    
    public function getProduitsCepage() 
    {

        return array($this->getHash() => $this);
    }
}
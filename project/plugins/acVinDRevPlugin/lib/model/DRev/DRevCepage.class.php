<?php
/**
 * Model for DRevCepage
 *
 */

class DRevCepage extends BaseDRevCepage {
    
    public function getChildrenNode() 
    {
        return $this->detail;
    }

    public function getCouleur() {

        return $this->getParent();
    }

    public function reorderByConf() {

        return null;
    }

    public function getProduitHash() {

        return $this->getCouleur()->getProduitHash();
    }

    public function addDetailNode($lieu = null) {
        if(!$this->getConfig()->hasLieuEditable()) {
            $lieu = null;
        }

        $detail = $this->getDetailNode($lieu);
        if($detail) {

            return $detail;
        }

        $detail = $this->detail->add();
        $detail->lieu = $lieu;
        $detail->getLibelle();

        return $detail;
    }

    public function getDetailNode($lieu = null) {
       foreach ($this->detail as $detail) {
            if(is_null($lieu)) {

                return $detail;
            }

            if($detail->exist('lieu') && trim(strtolower($detail->lieu) == trim(strtolower($lieu)))) {                
             
                return $detail;
            }
        }

        return null;
    }
}

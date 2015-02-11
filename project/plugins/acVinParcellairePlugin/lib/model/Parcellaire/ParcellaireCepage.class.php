<?php

/**
 * Model for ParcellaireCepage
 *
 */
class ParcellaireCepage extends BaseParcellaireCepage {

    public function getChildrenNode() {
        return $this->detail;
    }

    public function getCouleur() {

        return $this->getParent();
    }

    public function getProduitHash() {

        return $this->getCouleur()->getProduitHash();
    }

    public function getProduits($onlyActive = false) {
        if ($onlyActive && !$this->isActive()) {

            return array();
        }

        return array($this->getHash() => $this);
    }

    public function getAppellation() {
        return $this->getCouleur()->getAppellation();
    }

    public function addDetailNode($key, $superficie = null) {
//        if(!$this->getConfig()->hasLieuEditable()) {
//            $lieu = null;
//        }

        $detail = $this->getDetailNode($key);
        if($detail) {

            return $detail;
        }

        $detail = $this->detail->add($key);
        $detail->superficie = $superficie;
        return $detail;
    }
    
    public function getDetailNode($key) {
       foreach ($this->detail as $parcelleKey => $detail) {
            

            if($parcelleKey ==  $key) {                
             
                return $detail;
            }
        }

        return null;
    }
    
}

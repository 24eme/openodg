<?php

/**
 * Model for ParcellaireCouleur
 *
 */
class ParcellaireCouleur extends BaseParcellaireCouleur {

    public function getChildrenNode() {
        return $this->getCepages();
    }

    public function getCepages() {

        return $this->filter('^cepage_');
    }

    public function getLieu() {
        return $this->getParent();
    }

    public function getMention() {
        return $this->getLieu()->getMention();
    }

    public function getAppellation() {
        return $this->getMention()->getAppellation();
    }   
    
    public function getProduitHash() {
        if(!$this->getMention()->getConfig()->hasManyNoeuds()) {

            return $this->getHash();
        }


        return $this->getMention()->getHash()."/lieu/".$this->getKey();
    }
    
}

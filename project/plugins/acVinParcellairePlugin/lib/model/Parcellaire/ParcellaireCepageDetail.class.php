<?php

/**
 * Model for ParcellaireCepageDetail
 *
 */
class ParcellaireCepageDetail extends BaseParcellaireCepageDetail {

    public function getGenre() {
        return $this->getParent();
    }

    public function getChildrenNode() {
        return $this->getMentions();
    }

    public function getMentions() {
        return $this->filter('^mention');
    }

    public function getProduitsCepageDetails() {

        return array($this->getHash() => $this);
    }

    public function getLieuLibelle() {
        if($this->lieu) {

            return $this->lieu;
        }

        return $this->getLieuNode()->getLibelle();
    }

    public function getParcelleIdentifiant() {
        return $this->commune . ' ' . $this->section . ' ' . $this->numero_parcelle;
    }

    public function getAppellation() {
        return $this->getCepage()->getAppellation();
    }

    public function getCepage() {

        return $this->getParent()->getParent();
    }

    public function getCouleur() {

        return $this->getCepage()->getCouleur();
    }
    
     public function isCleanable() {        
        return !$this->superficie;
    }

    public function getLieuNode() {

        return $this->getCouleur()->getLieu();
    }

    public function cleanNode() {

        return false;
    }

}

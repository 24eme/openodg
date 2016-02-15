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

    public function addAcheteur($acheteur) {
        
        return $this->getCepage()->addAcheteurFromNode($acheteur, $this->lieu);
    }

    public function getAcheteurs() {

        return $this->getCepage()->getAcheteursNode($this->lieu);
    }

    public function getAcheteursByCVI() {
        $acheteurs = array();
        foreach($this->getAcheteurs() as $type => $acheteurs) {
            foreach($acheteurs as $cvi => $acheteur) {
                $acheteurs[$cvi] = $acheteur; 
            }
        }

        return $acheteurs;
    }

    public function getProduitsCepageDetails($onlyVtSgn = false) {

        return array($this->getHash() => $this);
    }

    public function getLibelleComplet() {
        return $this->getAppellation()->getLibelleComplet().' '.$this->getLieuLibelle().' '.$this->getCepageLibelle();
    }
    
    public function getLieuLibelle() {
        if ($this->lieu) {

            return $this->lieu;
        }

        return $this->getLieuNode()->getLibelle();
    }

    public function getParcelleIdentifiant() {
        return sprintf('%s %03s %03s', $this->commune, $this->section, $this->numero_parcelle);
    }

    public function getAppellation() {
        return $this->getCepage()->getAppellation();
    }

    public function getCepage() {

        return $this->getParent()->getParent();
    }

    public function getCepageLibelle() {

        return $this->getCepage()->getLibelle();
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

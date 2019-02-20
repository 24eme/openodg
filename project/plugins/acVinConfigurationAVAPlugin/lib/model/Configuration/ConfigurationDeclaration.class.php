<?php

class ConfigurationDeclaration extends BaseConfigurationDeclaration {

    public function getChildrenNode() {

        return $this->getCertifications();
    }

    public function getCertifications() {

        return $this->filter('^certification');
    }

    public function getNoeudAppellations() {

        return $this->getChildrenNodeDeep(2);
    }

    public function getHashRelation($key) {
        
        return "/".$this->getKeyRelation($key);
    }

    public function hasNoUsagesIndustriels() {
        
        return ($this->exist('no_usages_industriels') && $this->get('no_usages_industriels'));
    }

    public function hasNoRecapitulatifCouleur() {
        
        return ($this->exist('no_recapitulatif_couleur') && $this->get('no_recapitulatif_couleur'));
    }

    public function getRendementAppellation() {

        return 0;
    }

    public function getRendementCouleur() {

        return 0;
    }

    public function getRendement() {

        return 0;
    }

    public function getRendementVci() {

        return 0;
    }

    public function getRendementVciTotal() {

        return 0;
    }

    public function hasMout() {

        return false;
    }

    public function hasTotalCepage() {

        return true;
    }

    public function hasVtsgn() {

        return true;
    }
    
    public function isAutoDs() {
        
        return false;
    }

    public function isAutoDRev() {
        
        return false;
    }
}
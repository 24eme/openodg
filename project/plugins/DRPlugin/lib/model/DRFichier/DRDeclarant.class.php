<?php

class DRDeclarant extends BaseDRDeclarant {

    public function getPpm() {
        if(!$this->_get('ppm')) {
            $this->ppm = $this->getDocument()->getEtablissementObject()->ppm;
        }

        return $this->_get('ppm');
    }

    public function getSiret() {
        if(!$this->_get('siret')) {
            $this->siret = $this->getDocument()->getEtablissementObject()->siret;
        }

        return Anonymization::hideIfNeeded($this->_get('siret'));
    }

    public function getTelephone() {
        if($this->exist('telephone') && $this->_get('telephone')) {

            return  Anonymization::hideIfNeeded($this->_get('telephone'));
        }

        return ($this->_get('telephone_bureau')) ?  Anonymization::hideIfNeeded($this->_get('telephone_bureau')) :  Anonymization::hideIfNeeded($this->_get('telephone_mobile'));
    }

    public function getTelephoneMobile() {
        return Anonymization::hideIfNeeded($this->_get('telephone_mobile'));
    }
    public function getTelephoneBureau() {
        return Anonymization::hideIfNeeded($this->_get('telephone_bureau'));
    }
    public function getFax() {
        return Anonymization::hideIfNeeded($this->_get('fax'));
    }
    public function getEmail() {
        return Anonymization::hideIfNeeded($this->_get('email'));
    }
    public function getNom() {
        return Anonymization::hideIfNeeded($this->_get('nom'));
    }
    public function getAdresse() {
        return Anonymization::hideIfNeeded($this->_get('adresse'));
    }
    public function getRaisonSociale() {
        return Anonymization::hideIfNeeded($this->_get('raison_sociale'));
    }

}

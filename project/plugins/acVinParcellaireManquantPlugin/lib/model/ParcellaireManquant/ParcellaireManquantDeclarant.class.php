<?php

class ParcellaireManquantDeclarant extends BaseParcellaireManquantDeclarant {
    public function getTelephoneBureau(){
        return Anonymization::hideIfNeeded($this->_get('telephone_bureau'));
    }
    public function getTelephoneMobile(){
        return Anonymization::hideIfNeeded($this->_get('telephone_mobile'));
    }
    public function getFax(){
        return Anonymization::hideIfNeeded($this->_get('fax'));
    }
    public function getEmail(){
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

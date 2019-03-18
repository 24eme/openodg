<?php
/**
 * Model for ParcellaireDeclarant
 *
 */

class ParcellaireDeclarant extends BaseParcellaireDeclarant {
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
}

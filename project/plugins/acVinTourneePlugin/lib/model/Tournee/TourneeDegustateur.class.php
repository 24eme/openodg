<?php
/**
 * Model for TourneeDegustateur
 *
 */

class TourneeDegustateur extends BaseTourneeDegustateur {

    public function getNom() {
        return Anonymization::hideIfNeeded($this->_get('nom'));
    }
    public function getEmail(){
        return Anonymization::hideIfNeeded($this->_get('email'));
    }
    public function getAdresse() {
        return Anonymization::hideIfNeeded($this->_get('adresse'));
    }
}

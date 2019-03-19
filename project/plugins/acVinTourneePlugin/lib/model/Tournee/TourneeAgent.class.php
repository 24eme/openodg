<?php
/**
 * Model for TourneeAgent
 *
 */

class TourneeAgent extends BaseTourneeAgent {

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

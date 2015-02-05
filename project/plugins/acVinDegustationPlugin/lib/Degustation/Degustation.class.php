<?php
/**
 * Model for Degustation
 *
 */

class Degustation extends BaseDegustation {
    
    public function constructId() {
        $this->buildIdentifiant();
        $this->set('_id', 'DEG-' . $this->identifiant);
    }

    public function setDate($date) {
        $this->buildDatesPrelevements();

        return $this->_set('date', $date);
    }

    public function buildIdentifiant() {

        $this->identifiant = sprintf("%s-%s", str_replace("-", "", $this->date), $this->appellation);
    }

    public function buildDatesPrelevements() {

    }
    
}
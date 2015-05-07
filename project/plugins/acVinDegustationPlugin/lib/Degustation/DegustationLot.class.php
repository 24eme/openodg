<?php
/**
 * Model for DegustationLot
 *
 */

class DegustationLot extends BaseDegustationLot {

    public function getLibelleComplet() {
        if(!$this->_get("libelle_complet")) {

            return $this->getLibelle();
        }

        return $this->_get('libelle_complet');
    }
}
<?php
/**
 * Model for ParcellaireIrrigableDeclaration
 *
 */

class ParcellaireIrrigableDeclaration extends BaseParcellaireIrrigableDeclaration {

    public function getParcellesByCommune() {
        $parcelles = array();

        foreach($this->getDocument()->getParcelles() as $parcelle) {
            if(!isset($parcelles[$parcelle->commune])) {
                $parcelles[$parcelle->commune] = array();
            }
            $parcelles[$parcelle->commune][$parcelle->getParcelleId()] = $parcelle;
        }

        ksort($parcelles);
        return $parcelles;
    }
}

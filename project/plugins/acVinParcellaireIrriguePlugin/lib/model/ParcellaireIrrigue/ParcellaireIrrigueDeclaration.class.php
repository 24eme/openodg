<?php
/**
 * Model for ParcellaireIrrigueDeclaration
 *
 */

class ParcellaireIrrigueDeclaration extends BaseParcellaireIrrigueDeclaration {

    public function getParcellesByCommune() {
        $parcelles = array();

        foreach($this->getDocument()->getParcelles() as $parcelle) {
            if(!isset($parcelles[$parcelle->commune])) {
                $parcelles[$parcelle->commune] = array();
            }
            $parcelles[$parcelle->commune][$parcelle->getHash()] = $parcelle;
        }

        ksort($parcelles);
        return $parcelles;
    }
}

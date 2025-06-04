<?php
/**
 * Model for ParcellaireIrrigableDeclaration
 *
 */

class ParcellaireIrrigableDeclaration extends BaseParcellaireIrrigableDeclaration {

    public function getParcellesByCommune() {
        $parcelles = array();

        foreach($this->getParcelles() as $parcelle) {
            if(!isset($parcelles[$parcelle->commune])) {
                $parcelles[$parcelle->commune] = array();
            }
            $parcelles[$parcelle->commune][$parcelle->getParcelleId()] = $parcelle;
        }

        ksort($parcelles);
        return $parcelles;
    }

    public function getParcelles() {
        $parcelles = array();
        foreach($this as $produit) {
            foreach ($produit->detail as $parcelle) {
                $parcelles[$parcelle->getParcelleId()] = $parcelle;
            }
        }

        return $parcelles;
    }
}

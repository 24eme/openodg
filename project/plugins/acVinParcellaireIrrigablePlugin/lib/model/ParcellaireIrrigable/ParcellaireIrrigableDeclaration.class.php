<?php
/**
 * Model for ParcellaireIrrigableDeclaration
 *
 */

class ParcellaireIrrigableDeclaration extends BaseParcellaireIrrigableDeclaration {
    public function getParcellesByCommune() {
        $parcelles = array();

        foreach($this as $produit) {
            foreach ($produit->detail as $parcelle) {
                if(!isset($parcelles[$parcelle->commune])) {
                    $parcelles[$parcelle->commune] = array();
                }
                $parcelles[$parcelle->commune][$parcelle->getHash()] = $parcelle;
            }
        }

        ksort($parcelles);

        return $parcelles;
    }
}

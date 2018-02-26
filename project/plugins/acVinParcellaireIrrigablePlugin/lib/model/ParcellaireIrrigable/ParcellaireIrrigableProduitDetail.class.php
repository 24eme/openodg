<?php
/**
 * Model for ParcellaireIrrigableProduitDetail
 *
 */

class ParcellaireIrrigableProduitDetail extends BaseParcellaireIrrigableProduitDetail {

    public function getProduit() {

        return $this->getParent()->getParent();
    }

    public function getProduitLibelle() {

        return $this->getProduit()->getLibelle();
    }
}

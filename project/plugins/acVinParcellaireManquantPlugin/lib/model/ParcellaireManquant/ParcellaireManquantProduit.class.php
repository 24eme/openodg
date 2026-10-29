<?php
/**
 * Model for ParcellaireManquantProduit
 *
 */

class ParcellaireManquantProduit extends BaseParcellaireManquantProduit {

    public function getConfig() {

        return $this->getCouchdbDocument()->getConfiguration()->get($this->getHash());
    }

}
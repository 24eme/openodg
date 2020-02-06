<?php
/**
 * Model for ParcellaireAffectationProduit
 *
 */

class ParcellaireAffectationProduit extends BaseParcellaireAffectationProduit {

    public function getConfig() {

        return $this->getCouchdbDocument()->getConfiguration()->get($this->getHash());
    }

}
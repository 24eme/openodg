<?php
/**
 * Model for ParcellaireIntentionAffectationProduit
 *
 */

class ParcellaireIntentionAffectationProduit extends BaseParcellaireIntentionAffectationProduit {

    public function getConfig() {

        return $this->getCouchdbDocument()->getConfiguration()->get($this->getHash());
    }

}
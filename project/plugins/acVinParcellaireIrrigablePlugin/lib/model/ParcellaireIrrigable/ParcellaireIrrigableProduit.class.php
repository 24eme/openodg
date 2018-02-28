<?php
/**
 * Model for ParcellaireIrrigableProduit
 *
 */

class ParcellaireIrrigableProduit extends BaseParcellaireIrrigableProduit {

    public function getConfig() {

        return $this->getCouchdbDocument()->getConfiguration()->get($this->getHash());
    }

}
<?php
/**
 * Model for ParcellaireIrrigueProduit
 *
 */

class ParcellaireIrrigueProduit extends BaseParcellaireIrrigueProduit {

    public function getConfig() {

        return $this->getCouchdbDocument()->getConfiguration()->get($this->getHash());
    }

}
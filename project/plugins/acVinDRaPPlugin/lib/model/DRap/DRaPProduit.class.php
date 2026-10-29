<?php
/**
 * Model for DRaPProduit
 *
 */

class DRaPProduit extends BaseDRaPProduit {

    public function getConfig() {

        return $this->getCouchdbDocument()->getConfiguration()->get($this->getHash());
    }

    public function getLibelle() {
        return $this->getConfig()->getLibelleComplet();
    }

}

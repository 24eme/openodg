<?php
/**
 * Model for DRapProduit
 *
 */

class DRapProduit extends BaseDRapProduit {

    public function getConfig() {

        return $this->getCouchdbDocument()->getConfiguration()->get($this->getHash());
    }

    public function getLibelle() {
        return $this->getConfig()->getLibelleComplet();
    }

}

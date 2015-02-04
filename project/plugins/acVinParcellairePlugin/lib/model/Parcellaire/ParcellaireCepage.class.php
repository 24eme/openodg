<?php

/**
 * Model for ParcellaireCepage
 *
 */
class ParcellaireCepage extends BaseParcellaireCepage {

    public function getChildrenNode() {
        return $this->detail;
    }

    public function getCouleur() {

        return $this->getParent();
    }

    public function getProduitHash() {

        return $this->getCouleur()->getProduitHash();
    }

}

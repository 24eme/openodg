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

    public function getProduits($onlyActive = false) {
        if ($onlyActive && !$this->isActive()) {

            return array();
        }

        return array($this->getHash() => $this);
    }

    public function getAppellation() {
        return $this->getCouleur()->getAppellation();
    }

}

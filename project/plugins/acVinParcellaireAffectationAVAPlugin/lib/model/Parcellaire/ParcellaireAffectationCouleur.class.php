<?php

/**
 * Model for ParcellaireCouleur
 *
 */
class ParcellaireAffectationCouleur extends BaseParcellaireAffectationCouleur {

    public function getChildrenNode() {
        return $this->getCepages();
    }

    public function getCepages() {

        return $this->filter('^cepage_');
    }

    public function getLieu() {
        return $this->getParent();
    }

    public function getMention() {
        return $this->getLieu()->getMention();
    }

    public function getAppellation() {
        return $this->getMention()->getAppellation();
    }

}

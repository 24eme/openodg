<?php

abstract class DeclarationConfiguration {

    public function getCampagneDebutMois() {

        return 8;
    }

    public function getCampagneManager() {
        if(is_null($this->campagneManager)) {
            $this->campagneManager = new CampagneManager(sprintf("%02d", $this->getCampagneDebutMois()).'-01',   CampagneManager::FORMAT_COMPLET);
        }

        return $this->campagneManager;
    }

    public function getCurrentCampagne() {

        return $this->getCampagneManager()->getCurrent();
    }

    public function getCurrentPeriode() {

        return $this->getCampagneManager()->getCurrentPeriode();
    }
}

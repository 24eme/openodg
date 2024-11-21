<?php

abstract class DeclarationConfiguration {

    protected $campagneManager = null;

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

    public function isModuleEnabled() {

        return in_array($this->getModuleName(), sfConfig::get('sf_enabled_modules'));
    }

    public function getDateOuvertureConfigName() {

        return $this->getModuleName();
    }

    public function getDateOuverture() {
        $dates = sfConfig::get('app_dates_ouverture_'.$this->getDateOuvertureConfigName());
        if (!is_array($dates) || !isset($dates['debut']) || !isset($dates['fin'])) {
            return array('debut'=>'1900-01-01', 'fin' => '9999-12-31');
        }
        return $dates;
    }

    public function getDateOuvertureDebut() {
        return $this->getDateOuverture()['debut'];
    }

    public function getDateOuvertureFin() {

        return $this->getDateOuverture()['fin'];
    }

    public function isOpen($date = null) {
        if(is_null($date)) {

            $date = date('Y-m-d');
        }

        return $date >= $this->getDateOuvertureDebut() && $date <= $this->getDateOuvertureFin();
    }
}

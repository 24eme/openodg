<?php

class ParcellaireIrrigueConfiguration extends DeclarationConfiguration {

    private static $_instance = null;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new ParcellaireIrrigueConfiguration();
        }
        return self::$_instance;
    }

    public function getCampagneDebutMois() {

        return ParcellaireAffectationConfiguration::getInstance()->getCampagneDebutMois();
    }

    public function getModuleName() {

        return 'parcellaireIrrigue';
    }

    public function getDateOuvertureConfigName() {

        return 'parcellaire_irrigue';
    }
}

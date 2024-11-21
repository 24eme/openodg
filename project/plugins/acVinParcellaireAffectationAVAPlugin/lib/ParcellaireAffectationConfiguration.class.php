<?php

class ParcellaireAffectationConfiguration extends DeclarationConfiguration {

    private static $_instance = null;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new ParcellaireAffectationConfiguration();
        }
        return self::$_instance;
    }

    public function getCampagneDebutMois() {

        return 2;
    }

    public function getModuleName() {
        return 'parcellaireAffectation';
    }

    public function getDateOuvertureConfigName() {

        return 'parcellaire';
    }
}

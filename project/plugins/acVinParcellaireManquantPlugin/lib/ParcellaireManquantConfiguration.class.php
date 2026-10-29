<?php

class ParcellaireManquantConfiguration extends DeclarationConfiguration {

    private static $_instance = null;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new ParcellaireManquantConfiguration();
        }
        return self::$_instance;
    }

    public function getCampagneDebutMois() {

        return  4;
    }

    public function getModuleName() {

        return 'parcellaireManquant';
    }

    public function getDateOuvertureConfigName() {

        return 'parcellaire_manquant';
    }
}

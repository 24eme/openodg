<?php

class ParcellaireAffectationCremantConfiguration extends ParcellaireAffectationConfiguration {

    private static $_instance = null;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new ParcellaireAffectationCremantConfiguration();
        }
        return self::$_instance;
    }

    public function getModuleName() {
        return 'parcellaireAffectationCremant';
    }

    public function getDateOuvertureConfigName() {

        return 'parcellaire_cremant';
    }
}

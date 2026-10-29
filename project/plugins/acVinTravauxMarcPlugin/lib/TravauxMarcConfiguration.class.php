<?php

class TravauxMarcConfiguration extends DeclarationConfiguration {

    private static $_instance = null;

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new TravauxMarcConfiguration();
        }
        return self::$_instance;
    }

    public function getCampagneDebutMois() {

        return DRevConfiguration::getInstance()->getCampagneDebutMois();
    }

    public function getModuleName() {

        return 'travauxmarc';
    }
}

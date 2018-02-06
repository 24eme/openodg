<?php

class ParcellaireIrrigableEtapes extends Etapes {


    const ETAPE_PARCELLES = 'parcelles';
    const ETAPE_VALIDATION = 'validation';

    private static $_instance = null;
    public static $etapes = array(
        self::ETAPE_PARCELLES => 1,
        self::ETAPE_VALIDATION => 2
    );

    public static $links = array(
        self::ETAPE_PARCELLES => 'parcellaire_parcelles',
        self::ETAPE_VALIDATION => 'parcellaire_validation'
    );

    public static $libelles = array(
        self::ETAPE_PARCELLES => 'Parcelles',
        self::ETAPE_VALIDATION => 'Validation'
    );

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new ParcellaireIrrigableEtapes();
        }
        return self::$_instance;
    }

    public function getEtapesHash() {
        return self::$etapes;
    }

    public function getRouteLinksHash() {
        return self::$links;
    }

    public function getLibellesHash() {
        return self::$libelles;
    }

}

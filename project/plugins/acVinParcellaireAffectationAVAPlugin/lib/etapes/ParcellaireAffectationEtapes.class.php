<?php

class ParcellaireAffectationEtapes extends Etapes {

    const ETAPE_EXPLOITATION = 'exploitation';
    const ETAPE_PROPRIETE = 'propriete';
    const ETAPE_PARCELLES = 'parcelles';
    const ETAPE_ACHETEURS = 'acheteurs';
    const ETAPE_VALIDATION = 'validation';

    private static $_instance = null;
    public static $etapes = array(
        self::ETAPE_EXPLOITATION => 1,
        self::ETAPE_PROPRIETE => 2,
        self::ETAPE_PARCELLES => 3,
        self::ETAPE_ACHETEURS => 4,
        self::ETAPE_VALIDATION => 5
    );

    public static $links = array(
        self::ETAPE_EXPLOITATION => 'parcellaire_exploitation',
        self::ETAPE_PROPRIETE => 'parcellaire_propriete',
        self::ETAPE_PARCELLES => 'parcellaire_parcelles',
        self::ETAPE_ACHETEURS => 'parcellaire_acheteurs',
        self::ETAPE_VALIDATION => 'parcellaire_validation'
    );

    public static $libelles = array(
        self::ETAPE_EXPLOITATION => 'Exploitation',
        self::ETAPE_PROPRIETE => 'Destination du raisin',
        self::ETAPE_PARCELLES => 'Parcelles',
        self::ETAPE_ACHETEURS => 'Acheteurs',
        self::ETAPE_VALIDATION => 'Validation'
    );

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new ParcellaireAffectationEtapes();
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

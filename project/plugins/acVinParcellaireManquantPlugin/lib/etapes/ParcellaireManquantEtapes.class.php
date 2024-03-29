<?php

class ParcellaireManquantEtapes extends Etapes {

	const ETAPE_EXPLOITATION = 'exploitation';
    const ETAPE_PARCELLES = 'parcelles';
    const ETAPE_SAISIEINFOS = 'manquants';
    const ETAPE_VALIDATION = 'validation';

    private static $_instance = null;
    public static $etapes = array(
        self::ETAPE_EXPLOITATION => 1,
        self::ETAPE_PARCELLES => 2,
        self::ETAPE_SAISIEINFOS => 3,
        self::ETAPE_VALIDATION => 4
    );

    public static $links = array(
        self::ETAPE_EXPLOITATION => 'parcellairemanquant_exploitation',
        self::ETAPE_PARCELLES => 'parcellairemanquant_parcelles',
        self::ETAPE_SAISIEINFOS => 'parcellairemanquant_manquants',
        self::ETAPE_VALIDATION => 'parcellairemanquant_validation'
    );

    public static $libelles = array(
        self::ETAPE_EXPLOITATION => 'Exploitation',
        self::ETAPE_PARCELLES => 'Sélection des parcelles',
        self::ETAPE_SAISIEINFOS => 'Déclaration des manquants',
        self::ETAPE_VALIDATION => 'Validation'
    );

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new ParcellaireManquantEtapes();
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

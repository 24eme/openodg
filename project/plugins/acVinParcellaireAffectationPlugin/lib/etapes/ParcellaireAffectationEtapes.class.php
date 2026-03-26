<?php

class ParcellaireAffectationEtapes extends Etapes {

	const ETAPE_EXPLOITATION = 'exploitation';
    const ETAPE_AFFECTATIONS = 'affectations';
    const ETAPE_MANQUANTS = 'manquants';
    const ETAPE_IRRIGATION = 'irrigation';
    const ETAPE_VALIDATION = 'validation';

    private static $_instance = null;

    public static $etapes = array(
        self::ETAPE_EXPLOITATION => 1,
        self::ETAPE_AFFECTATIONS => 2,
        self::ETAPE_VALIDATION => 3
    );

    public static $links = array(
        self::ETAPE_EXPLOITATION => 'parcellaireaffectation_exploitation',
        self::ETAPE_AFFECTATIONS => 'parcellaireaffectation_affectations',
        self::ETAPE_MANQUANTS => 'parcellaireaffectation_manquants',
        self::ETAPE_IRRIGATION => 'parcellaireaffectation_irrigation',
        self::ETAPE_VALIDATION => 'parcellaireaffectation_validation'
    );

    public static $libelles = array(
        self::ETAPE_EXPLOITATION => 'Exploitation',
        self::ETAPE_AFFECTATIONS => 'Affectations',
        self::ETAPE_MANQUANTS => 'Manquants',
        self::ETAPE_IRRIGATION => 'Irrigation',
        self::ETAPE_VALIDATION => 'Validation'
    );

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new ParcellaireAffectationEtapes();
        }
        return self::$_instance;
    }

    public function getEtapesHash() {
        if(ParcellaireConfiguration::getInstance()->hasDeclarationsLiees()) {
            return array(
                self::ETAPE_EXPLOITATION => 1,
                self::ETAPE_AFFECTATIONS => 2,
                self::ETAPE_MANQUANTS => 3,
                self::ETAPE_IRRIGATION => 4,
                self::ETAPE_VALIDATION => 5
            );
        }
        return self::$etapes;
    }

    public function getRouteLinksHash() {
        return self::$links;
    }

    public function getLibellesHash() {
        return self::$libelles;
    }

}

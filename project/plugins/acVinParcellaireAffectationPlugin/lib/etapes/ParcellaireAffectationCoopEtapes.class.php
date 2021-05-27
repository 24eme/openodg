<?php

class ParcellaireAffectationCoopEtapes extends Etapes {

    const ETAPE_APPORTEURS = 'apporteurs';
    const ETAPE_SAISIES = 'saisies';

    private static $_instance = null;
    public static $etapes = array(
        self::ETAPE_APPORTEURS => 1,
        self::ETAPE_SAISIES => 2
    );

    public static $links = array(
        self::ETAPE_APPORTEURS => 'parcellaireaffectationcoop_apporteurs',
        self::ETAPE_SAISIES => 'parcellaireaffectationcoop_liste'
    );

    public static $libelles = array(
        self::ETAPE_APPORTEURS => 'Apporteurs',
        self::ETAPE_SAISIES => 'Saisies des affectations parcellaires'
    );

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new ParcellaireAffectationCoopEtapes();
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

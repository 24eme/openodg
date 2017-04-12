<?php

class TourneeSaisieEtapes extends Etapes{

    const ETAPE_CREATION = 'CREATION';
    const ETAPE_SAISIE = 'SAISIE';
    const ETAPE_DEGUSTATEURS = 'DEGUSTATEURS';

    private static $_instance = null;
    public static $etapes = array(
        self::ETAPE_SAISIE => 1,
        self::ETAPE_DEGUSTATEURS => 2,
    );

    public static $libelles = array(
        self::ETAPE_SAISIE => 'Saisie',
        self::ETAPE_DEGUSTATEURS => 'Dégustateurs',
    );

    public static $links = array(
        self::ETAPE_SAISIE => 'degustation_saisie_creation',
        self::ETAPE_DEGUSTATEURS => 'degustation_degustateurs',
    );

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new TourneeSaisieEtapes();
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

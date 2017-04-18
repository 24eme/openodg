<?php

class TourneeSaisieEtapes extends Etapes{

    const ETAPE_CREATION = 'CREATION';
    const ETAPE_SAISIE = 'SAISIE';
    const ETAPE_SAISIE_DEGUSTATEURS = 'SAISIE_DEGUSTATEURS';

    private static $_instance = null;
    public static $etapes = array(
        self::ETAPE_SAISIE => 1,
        self::ETAPE_SAISIE_DEGUSTATEURS => 2,
    );

    public static $libelles = array(
        self::ETAPE_SAISIE => 'Prélèvements',
        self::ETAPE_SAISIE_DEGUSTATEURS => 'Dégustateurs',
    );

    public static $links = array(
        self::ETAPE_SAISIE => 'degustation_saisie',
        self::ETAPE_SAISIE_DEGUSTATEURS => 'degustation_saisie_degustateurs',
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

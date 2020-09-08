<?php

class DegustationEtapes extends Etapes {

    const ETAPE_LOTS = 'LOTS';
    const ETAPE_DEGUSTATEURS = 'DEGUSTATEURS';
    const ETAPE_VALIDATION = 'VALIDATION';

    // const ETAPE_ORGANISATION_TABLE = 'ORGANISATION_TABLES';
    // const ETAPE_RESULTATS = 'RESULTATS';

    private static $_instance = null;

    public static $etapes = array(
        self::ETAPE_LOTS => 1,
        self::ETAPE_DEGUSTATEURS => 2,
        self::ETAPE_VALIDATION => 3,
        // self::ETAPE_ORGANISATION_TABLE => 4,
        // self::ETAPE_RESULTATS => 5,
    );

    public static $libelles = array(
        self::ETAPE_LOTS => 'Lots',
        self::ETAPE_DEGUSTATEURS => 'Dégustateurs',
        self::ETAPE_VALIDATION => 'Validation',
        // self::ETAPE_ORGANISATION_TABLE => 'Organisation tables',
        // self::ETAPE_RESULTATS => 'Résultats',
    );

    public static $links = array(
        self::ETAPE_LOTS => 'degustation_prelevement_lots',
        self::ETAPE_DEGUSTATEURS => 'degustation_selection_degustateurs',
        self::ETAPE_VALIDATION => 'degustation_validation',
        // self::ETAPE_ORGANISATION_TABLE => 'degustation_organisation_table',
        // self::ETAPE_RESULTATS => 'degustation_resultats',
    );

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new DegustationEtapes();
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

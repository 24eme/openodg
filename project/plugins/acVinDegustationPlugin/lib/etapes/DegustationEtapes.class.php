<?php

class DegustationEtapes extends Etapes {

    const ETAPE_LOTS = 'LOTS';
    const ETAPE_DEGUSTATEURS = 'DEGUSTATEURS';
    const ETAPE_VALIDATION = 'VALIDATION';
    const ETAPE_PRELEVEMENTS = 'PRELEVEMENTS';
    const ETAPE_ANONYMATS = 'ANONYMATS';
    const ETAPE_TABLES = 'TABLES';

    private static $_instance = null;

    public static $etapes = array(
        self::ETAPE_LOTS => 1,
        self::ETAPE_DEGUSTATEURS => 2,
        self::ETAPE_VALIDATION => 3,
        self::ETAPE_PRELEVEMENTS => 4,
        self::ETAPE_ANONYMATS => 5,
        self::ETAPE_TABLES => 6
    );

    public static $libelles = array(
        self::ETAPE_LOTS => 'Lots',
        self::ETAPE_DEGUSTATEURS => 'Dégustateurs',
        self::ETAPE_VALIDATION => 'Validation',
        self::ETAPE_PRELEVEMENTS => 'Prélévements / Convocations',
        self::ETAPE_ANONYMATS => 'Anonymats',
        self::ETAPE_TABLES => 'Organisation tables',
    );

    public static $links = array(
        self::ETAPE_LOTS => 'degustation_prelevement_lots',
        self::ETAPE_DEGUSTATEURS => 'degustation_selection_degustateurs',
        self::ETAPE_VALIDATION => 'degustation_validation',
        self::ETAPE_PRELEVEMENTS => 'degustation_prelevements_etape',
        self::ETAPE_ANONYMATS => 'degustation_anonymats_etape',
        self::ETAPE_TABLES => 'degustation_tables_etape',
    );

    public static $etapesAfterValidation = array(
        self::ETAPE_LOTS => 0,
        self::ETAPE_DEGUSTATEURS => 0,
        self::ETAPE_VALIDATION => 0,
        self::ETAPE_PRELEVEMENTS => 1,
        self::ETAPE_ANONYMATS => 1,
        self::ETAPE_TABLES => 1
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

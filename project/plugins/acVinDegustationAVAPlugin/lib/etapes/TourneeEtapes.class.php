<?php

class TourneeEtapes extends Etapes{

    const ETAPE_CREATION = 'CREATION';
    const ETAPE_OPERATEURS = 'OPERATEURS';
    const ETAPE_DEGUSTATEURS = 'DEGUSTATEURS';
    const ETAPE_AGENTS = 'AGENTS';
    const ETAPE_PRELEVEMENTS = 'PRELEVEMENTS';
    const ETAPE_VALIDATION = 'VALIDATION';

    private static $_instance = null;
    public static $etapes = array(
        self::ETAPE_OPERATEURS => 1,
        self::ETAPE_DEGUSTATEURS => 2,
        self::ETAPE_AGENTS => 3,
        self::ETAPE_PRELEVEMENTS => 4,
        self::ETAPE_VALIDATION => 5,
    );

    public static $libelles = array(
        self::ETAPE_OPERATEURS => 'Opérateurs',
        self::ETAPE_DEGUSTATEURS => 'Dégustateurs',
        self::ETAPE_AGENTS => 'Agents',
        self::ETAPE_PRELEVEMENTS => 'Prélevements',
        self::ETAPE_VALIDATION => 'Validation',
    );

    public static $links = array(
        self::ETAPE_OPERATEURS => 'degustation_operateurs',
        self::ETAPE_DEGUSTATEURS => 'degustation_degustateurs',
        self::ETAPE_AGENTS => 'degustation_agents',
        self::ETAPE_PRELEVEMENTS => 'degustation_prelevements',
        self::ETAPE_VALIDATION => 'degustation_validation'
    );

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new TourneeEtapes();
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

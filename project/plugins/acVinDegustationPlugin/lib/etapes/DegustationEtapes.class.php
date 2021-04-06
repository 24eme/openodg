<?php

class DegustationEtapes extends Etapes {

    const ETAPE_LOTS = 'LOTS';
    const ETAPE_DEGUSTATEURS = 'DEGUSTATEURS';
    const ETAPE_PRELEVEMENTS = 'PRELEVEMENTS';
    const ETAPE_TABLES = 'TABLES';
    const ETAPE_ANONYMATS = 'ANONYMATS';
    const ETAPE_COMMISSION = 'COMMISSION';
    const ETAPE_RESULTATS = 'RESULTATS';
    const ETAPE_NOTIFICATIONS = 'NOTIFICATIONS';

    private static $_instance = null;

    public static $etapes = array(
        self::ETAPE_LOTS => 1,
        self::ETAPE_DEGUSTATEURS => 2,
        self::ETAPE_PRELEVEMENTS => 3,
        self::ETAPE_TABLES => 4,
        self::ETAPE_ANONYMATS => 5,
        self::ETAPE_COMMISSION => 6,
        self::ETAPE_RESULTATS => 7,
        self::ETAPE_NOTIFICATIONS => 8,
    );

    public static $libelles = array(
        self::ETAPE_LOTS => 'Lots',
        self::ETAPE_DEGUSTATEURS => 'Dégustateurs',
        self::ETAPE_PRELEVEMENTS => 'Prélévements / Confirmation',
        self::ETAPE_TABLES => 'Tables',
        self::ETAPE_ANONYMATS => 'Anonymats',
        self::ETAPE_COMMISSION => 'Commission',
        self::ETAPE_RESULTATS => 'Résultats / Présences',
        self::ETAPE_NOTIFICATIONS => 'Notifications'
    );

    public static $libelles_short = array(
        self::ETAPE_LOTS => 'Lots',
        self::ETAPE_DEGUSTATEURS => 'Dégustateurs',
        self::ETAPE_PRELEVEMENTS => 'Prélévements',
        self::ETAPE_TABLES => 'Tables',
        self::ETAPE_ANONYMATS => 'Anonymats',
        self::ETAPE_COMMISSION => 'Commission',
        self::ETAPE_RESULTATS => 'Résultats',
        self::ETAPE_NOTIFICATIONS => 'Notifications'
    );

    public static $links = array(
        self::ETAPE_LOTS => 'degustation_prelevement_lots',
        self::ETAPE_DEGUSTATEURS => 'degustation_selection_degustateurs',
        self::ETAPE_PRELEVEMENTS => 'degustation_prelevements_etape',
        self::ETAPE_TABLES => 'degustation_tables_etape',
        self::ETAPE_ANONYMATS => 'degustation_anonymats_etape',
        self::ETAPE_COMMISSION => 'degustation_commission_etape',
        self::ETAPE_RESULTATS => 'degustation_resultats_etape',
        self::ETAPE_NOTIFICATIONS => 'degustation_notifications_etape'
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

    public function isEtapeDisabled($etape, $doc) {

        $etapeAnonymat = self::$etapes[self::ETAPE_ANONYMATS];

        if($doc->isAnonymized() &&  self::$etapes[$doc->etape] >= $etapeAnonymat){
            return self::$etapes[$etape] < $etapeAnonymat;
        }

        return false;
    }

    public function getDefaultStep(){
      return self::ETAPE_NOTIFICATIONS;
    }

}

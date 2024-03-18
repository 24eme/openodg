<?php

class DegustationEtapes extends Etapes {

    const ETAPE_LOTS = 'LOTS';
    const ETAPE_DEGUSTATEURS = 'DEGUSTATEURS';
    const ETAPE_CONVOCATIONS = 'CONVOCATIONS';
    const ETAPE_PRELEVEMENTS = 'PRELEVEMENTS';
    const ETAPE_ORGANISATION = 'ORGANISATION';
    const ETAPE_TOURNEES = 'TOURNEES';
    const ETAPE_PRELEVEMENT_MANUEL = 'PRELEVEMENT_MANUEL';
    const ETAPE_ANONYMISATION_MANUELLE = 'ANONYMISATION_MANUELLE';
    const ETAPE_TABLES = 'TABLES';
    const ETAPE_ANONYMATS = 'ANONYMATS';
    const ETAPE_COMMISSION = 'COMMISSION';
    const ETAPE_RESULTATS = 'RESULTATS';
    const ETAPE_NOTIFICATIONS = 'NOTIFICATIONS';
    const ETAPE_VISUALISATION = 'VISUALISATION';

    private static $_instance = null;

    public static $etapes = array(
        self::ETAPE_LOTS => 1,
        self::ETAPE_DEGUSTATEURS => 2,
        self::ETAPE_CONVOCATIONS => 3,
        self::ETAPE_ORGANISATION => 3.5,
        self::ETAPE_TOURNEES => 4,
        self::ETAPE_PRELEVEMENTS => 5,
        self::ETAPE_ANONYMISATION_MANUELLE => 5,
        self::ETAPE_TABLES => 6,
        self::ETAPE_ANONYMATS => 7,
        self::ETAPE_COMMISSION => 8,
        self::ETAPE_RESULTATS => 9,
        self::ETAPE_NOTIFICATIONS => 10,
        self::ETAPE_VISUALISATION => 11,
    );

    public static $libelles = array(
        self::ETAPE_LOTS => 'Lots',
        self::ETAPE_DEGUSTATEURS => 'Dégustateurs',
        self::ETAPE_CONVOCATIONS => 'Convocations',
        self::ETAPE_ORGANISATION => 'Organisation',
        self::ETAPE_TOURNEES => 'Tournées',
        self::ETAPE_PRELEVEMENTS => 'Prélévements',
        self::ETAPE_ANONYMISATION_MANUELLE => 'Anonymats',
        self::ETAPE_TABLES => 'Tables',
        self::ETAPE_ANONYMATS => 'Anonymats',
        self::ETAPE_COMMISSION => 'Commission',
        self::ETAPE_RESULTATS => 'Résultats',
        self::ETAPE_NOTIFICATIONS => 'Notifications',
        self::ETAPE_VISUALISATION => 'Visualisation',
    );

    public static $libelles_short = array(
        self::ETAPE_LOTS => 'Lots',
        self::ETAPE_DEGUSTATEURS => 'Dégustateurs',
        self::ETAPE_CONVOCATIONS => 'Convocations',
        self::ETAPE_ORGANISATION => 'Organisation',
        self::ETAPE_TOURNEES => 'Tournées',
        self::ETAPE_PRELEVEMENTS => 'Prélévements',
        self::ETAPE_ANONYMISATION_MANUELLE => 'Anonymats',
        self::ETAPE_TABLES => 'Tables',
        self::ETAPE_ANONYMATS => 'Anonymats',
        self::ETAPE_COMMISSION => 'Commission',
        self::ETAPE_RESULTATS => 'Résultats',
        self::ETAPE_NOTIFICATIONS => 'Notifications',
        self::ETAPE_VISUALISATION => 'Visualisation',
    );

    public static $links = array(
        self::ETAPE_LOTS => 'degustation_selection_lots',
        self::ETAPE_DEGUSTATEURS => 'degustation_selection_degustateurs',
        self::ETAPE_CONVOCATIONS => 'degustation_convocations',
        self::ETAPE_ORGANISATION => 'degustation_organisation_etape',
        self::ETAPE_TOURNEES => 'degustation_tournees_etape',
        self::ETAPE_PRELEVEMENT_MANUEL => 'degustation_prelevements_manuel_etape',
        self::ETAPE_PRELEVEMENTS => 'degustation_preleve',
        self::ETAPE_ANONYMISATION_MANUELLE => 'degustation_anonymats_etape',
        self::ETAPE_TABLES => 'degustation_tables_etape',
        self::ETAPE_ANONYMATS => 'degustation_anonymats_etape',
        self::ETAPE_COMMISSION => 'degustation_commission_etape',
        self::ETAPE_RESULTATS => 'degustation_resultats_etape',
        self::ETAPE_NOTIFICATIONS => 'degustation_notifications_etape',
        self::ETAPE_VISUALISATION => 'degustation_visualisation',
    );


    public static function getInstance(Degustation $degustation = null) {
        if (is_null(self::$_instance)) {
            if (is_null($degustation)) {
                throw new Exception("Degustation ne doit pas être nul lors de la 1ere instanciation");
            }

            switch (get_class($degustation)) {
                case "Tournee":
                    self::$_instance = new TourneeDegustationEtapes();
                    break;
                case "Degustation":
                default:
                    self::$_instance = new DegustationEtapes();
            }
        }
        return self::$_instance;
    }

    public function getEtapesHash()
    {
        return $this->filter(self::$etapes);
    }

    public function getRouteLinksHash() {
        return $this->filter(self::$links);
    }

    public function getLibellesHash() {
        return $this->filter(self::$libelles);
    }

    protected function filter($items)
    {
        if (DegustationConfiguration::getInstance()->isAnonymisationManuelle()) {
            unset($items[self::ETAPE_ANONYMATS]);
            unset($items[self::ETAPE_PRELEVEMENT_MANUEL]);
        } else {
            unset($items[self::ETAPE_ORGANISATION]);
            unset($items[self::ETAPE_ANONYMISATION_MANUELLE]);
        }

        if(DegustationConfiguration::getInstance()->isDegustationAutonome()) {
            unset($items[self::ETAPE_ORGANISATION]);
            unset($items[self::ETAPE_TOURNEES]);
            unset($items[self::ETAPE_PRELEVEMENTS]);
        }

        return $items;
    }

    public function isEtapeDisabled($etape, $doc)
    {
        if(DegustationConfiguration::getInstance()->isAnonymisationManuelle()) {
            return false;
        }

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

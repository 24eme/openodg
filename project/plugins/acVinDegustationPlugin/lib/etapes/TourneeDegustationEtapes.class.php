<?php

class TourneeDegustationEtapes extends DegustationEtapes
{
    const ETAPE_LOTS = 'LOTS';
    const ETAPE_ORGANISATION = 'ORGANISATION';
    const ETAPE_TOURNEES = 'TOURNEES';
    const ETAPE_SAISIE = 'SAISIE';
    const ETAPE_PRELEVEMENTS = 'PRELEVEMENTS';
    const ETAPE_VISUALISATION = 'VISUALISATION';

    private static $_instance = null;

    public static $etapes = [
        self::ETAPE_LOTS => 1,
        self::ETAPE_ORGANISATION => 2,
        self::ETAPE_TOURNEES => 3,
        self::ETAPE_SAISIE => 4,
        self::ETAPE_PRELEVEMENTS => 5,
        self::ETAPE_VISUALISATION => 6
    ];

    public static $libelles = [
        self::ETAPE_LOTS => 'Lots',
        self::ETAPE_ORGANISATION => 'Organisation',
        self::ETAPE_TOURNEES => 'Tournées',
        self::ETAPE_SAISIE => 'Saisie',
        self::ETAPE_PRELEVEMENTS => 'Prélèvements',
        self::ETAPE_VISUALISATION => 'Visualisation'
    ];

    public static $libelles_short = [
        self::ETAPE_LOTS => 'Lots',
        self::ETAPE_ORGANISATION => 'Organisation',
        self::ETAPE_TOURNEES => 'Tournées',
        self::ETAPE_SAISIE => 'Saisie',
        self::ETAPE_PRELEVEMENTS => 'Prélèvements',
        self::ETAPE_VISUALISATION => 'Visualisation'
    ];

    public static $links = [
        self::ETAPE_LOTS => 'degustation_selection_operateurs',
        self::ETAPE_ORGANISATION => 'degustation_organisation_etape',
        self::ETAPE_TOURNEES => 'degustation_tournees_etape',
        self::ETAPE_SAISIE => 'degustation_saisie_etape',
        self::ETAPE_PRELEVEMENTS => 'degustation_preleve',
        self::ETAPE_VISUALISATION => 'degustation_visualisation'
    ];

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
        return $items;
    }
}

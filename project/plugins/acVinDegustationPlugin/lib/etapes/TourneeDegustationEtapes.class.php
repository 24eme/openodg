<?php

class TourneeDegustationEtapes extends DegustationEtapes
{
    const ETAPE_LOTS = 'LOTS';
    const ETAPE_TOURNEES = 'TOURNEES';
    const ETAPE_PRELEVEMENTS = 'PRELEVEMENTS';

    private static $_instance = null;

    public static $etapes = [
        self::ETAPE_LOTS => 1,
        self::ETAPE_TOURNEES => 2,
        self::ETAPE_PRELEVEMENTS => 3
    ];

    public static $libelles = [
        self::ETAPE_LOTS => 'Lots',
        self::ETAPE_TOURNEES => 'Tournées',
        self::ETAPE_PRELEVEMENTS => 'Prélèvements'
    ];

    public static $libelles_short = [
        self::ETAPE_LOTS => 'Lots',
        self::ETAPE_TOURNEES => 'Tournées',
        self::ETAPE_PRELEVEMENTS => 'Prélèvements'
    ];

    public static $links = [
        self::ETAPE_LOTS => 'degustation_selection_operateurs',
        self::ETAPE_TOURNEES => 'degustation_tournees_etape',
        self::ETAPE_PRELEVEMENTS => 'degustation_prelevements_manuel_etape'
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

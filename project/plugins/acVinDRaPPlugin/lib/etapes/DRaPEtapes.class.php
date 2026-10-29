<?php

class DRaPEtapes extends Etapes {

	const ETAPE_EXPLOITATION = 'exploitation';
    const ETAPE_PARCELLES = 'parcelles';
    const ETAPE_DESTINATIONS = 'destinations';
    const ETAPE_VALIDATION = 'validation';

    private static $_instance = null;
    public static $etapes = array(
        self::ETAPE_EXPLOITATION => 1,
        self::ETAPE_PARCELLES => 2,
        self::ETAPE_DESTINATIONS => 3,
        self::ETAPE_VALIDATION => 4
    );

    public static $links = array(
        self::ETAPE_EXPLOITATION => 'drap_exploitation',
        self::ETAPE_PARCELLES => 'drap_parcelles',
        self::ETAPE_DESTINATIONS => 'drap_destinations',
        self::ETAPE_VALIDATION => 'drap_validation'
    );

    public static $libelles = array(
        self::ETAPE_EXPLOITATION => 'Exploitation',
        self::ETAPE_PARCELLES => 'Sélection des parcelles en renonciation à produire',
        self::ETAPE_DESTINATIONS => 'Nouvelles destinations des parcelles',
        self::ETAPE_VALIDATION => 'Validation'
    );

    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new DRaPEtapes();
        }
        return self::$_instance;
    }

    protected function filterItems($items) {
        if(ParcellaireConfiguration::getInstance()->hasDRaP() == false) {
            unset($items[self::ETAPE_DESTINATIONS]);
        }
        return $items;
    }

    public function getEtapesHash() {
        return $this->filterItems(self::$etapes);
    }

    public function getRouteLinksHash() {
        return $this->filterItems(self::$links);
    }

    public function getLibellesHash() {
        return self::$libelles;
    }

}

<?php

class TirageEtapes extends Etapes
{
    const ETAPE_EXPLOITATION = 'exploitation';
    const ETAPE_VIN = 'vin';
    const ETAPE_LOTS = 'lots';
    const ETAPE_VALIDATION = 'validation';
    
    private static $_instance = null;
    
    public static $etapes = array(
            self::ETAPE_EXPLOITATION => 1,
            self::ETAPE_VIN => 2,
            self::ETAPE_LOTS => 3,
            self::ETAPE_VALIDATION => 4,
    );
    
    public static $links = array(
            self::ETAPE_EXPLOITATION => 'tirage_exploitation',
            self::ETAPE_VIN => 'tirage_vin',
            self::ETAPE_LOTS => 'tirage_lots',
            self::ETAPE_VALIDATION => 'tirage_validation',
    );
    
    public static $libelles = array(
            self::ETAPE_EXPLOITATION => 'Exploitation',
            self::ETAPE_VIN => 'Caractéristiques',
            self::ETAPE_LOTS => 'Répartition',
            self::ETAPE_VALIDATION => 'Validation',
    );
    
    public static function getInstance() 
    {
        if(is_null(self::$_instance)) {
            self::$_instance = new TirageEtapes();
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

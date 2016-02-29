<?php
class DrevMarcEtapes extends Etapes
{
	const ETAPE_EXPLOITATION = 'exploitation';
	const ETAPE_REVENDICATION = 'revendication';
	const ETAPE_VALIDATION = 'validation';
	
	private static $_instance = null;
	
	public static $etapes = array(
            self::ETAPE_EXPLOITATION => 1,
            self::ETAPE_REVENDICATION => 2,
            self::ETAPE_VALIDATION => 3,
    );
    
	public static $links = array(
            self::ETAPE_EXPLOITATION => 'drevmarc_exploitation',
            self::ETAPE_REVENDICATION => 'drevmarc_revendication',
            self::ETAPE_VALIDATION => 'drevmarc_validation',
    );
    
	public static $libelles = array(
            self::ETAPE_EXPLOITATION => 'Exploitation',
            self::ETAPE_REVENDICATION => 'Revendication',
            self::ETAPE_VALIDATION => 'Validation',
    );
    
	public static function getInstance() 
	{
		if(is_null(self::$_instance)) {
			self::$_instance = new DrevMarcEtapes();
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

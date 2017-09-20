<?php
class HabilitationEtapes extends Etapes
{
	const ETAPE_EDITION = 'edition';
	const ETAPE_VALIDATION = 'validation';

	public static $etapes = array(
            self::ETAPE_EDITION => 1,
            self::ETAPE_VALIDATION => 2
    );

	public static $links = array(
            self::ETAPE_EDITION => 'habilitation_edition',
            self::ETAPE_VALIDATION => 'habilitation_validation'
    );

	public static $libelles = array(
            self::ETAPE_EDITION => "Edition",
            self::ETAPE_VALIDATION => "Validation"
    );

	private static $_instance = null;

	public static function getInstance()
	{
		if(is_null(self::$_instance)) {
			self::$_instance = new HabilitationEtapes();
		}
		return self::$_instance;
	}


  public function getEtapesHash()
  {
      return self::$etapes;
  }

  public function getRouteLinksHash()
  {
    return self::$links;
  }

  public function getLibellesHash()
  {
    return self::$libelles;
  }

}

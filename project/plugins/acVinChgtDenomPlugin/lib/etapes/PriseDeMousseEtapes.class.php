<?php
class PriseDeMousseEtapes extends Etapes
{
	const ETAPE_EDITION = 'edition';
	const ETAPE_VALIDATION = 'validation';

	public static $etapes = array(
            self::ETAPE_EDITION => 2,
            self::ETAPE_VALIDATION => 3
    );

	public static $links = array(
            self::ETAPE_EDITION => 'prisedemousse_edition',
            self::ETAPE_VALIDATION => 'prisedemousse_validation'
    );

	public static $libelles = array(
            self::ETAPE_EDITION => "Prise de mousse",
            self::ETAPE_VALIDATION => "Validation"
    );

	private static $_instance = null;

	public static function getInstance()
	{
		if(is_null(self::$_instance)) {
			self::$_instance = new PriseDeMousseEtapes();
		}
		return self::$_instance;
	}

	protected function filterItems($items) {
        return $items;
	}

    public function getEtapesHash()
    {

        return $this->filterItems(self::$etapes);
    }

    public function getRouteLinksHash()
    {

		return $this->filterItems(self::$links);
    }

    public function getLibellesHash()
    {

		return $this->filterItems(self::$libelles);
    }

}

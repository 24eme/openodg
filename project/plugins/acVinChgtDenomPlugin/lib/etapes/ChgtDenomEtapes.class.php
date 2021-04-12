<?php
class ChgtDenomEtapes extends Etapes
{
    const ETAPE_LOTS = 'lots';
	const ETAPE_EDITION = 'edition';
	const ETAPE_VALIDATION = 'validation';

	public static $etapes = array(
            self::ETAPE_LOTS => 1,
            self::ETAPE_EDITION => 2,
            self::ETAPE_VALIDATION => 3
    );

	public static $links = array(
            self::ETAPE_LOTS => 'chgtdenom_lots',
            self::ETAPE_EDITION => 'chgtdenom_edition',
            self::ETAPE_VALIDATION => 'chgtdenom_validation'
    );

	public static $libelles = array(
            self::ETAPE_LOTS => "Choix du lot",
            self::ETAPE_EDITION => "Changement / Déclassement",
            self::ETAPE_VALIDATION => "Validation"
    );

	private static $_instance = null;

	public static function getInstance()
	{
		if(is_null(self::$_instance)) {
			self::$_instance = new ChgtDenomEtapes();
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

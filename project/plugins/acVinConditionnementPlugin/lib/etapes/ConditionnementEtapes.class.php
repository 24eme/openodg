<?php
class ConditionnementEtapes extends Etapes
{
	const ETAPE_EXPLOITATION = 'exploitation';
	const ETAPE_LOTS = 'lots';
	const ETAPE_VALIDATION = 'validation';

	public static $etapes = array(
            self::ETAPE_EXPLOITATION => 1,
			self::ETAPE_LOTS => 2,
            self::ETAPE_VALIDATION => 3
    );

	public static $links = array(
            self::ETAPE_EXPLOITATION => 'conditionnement_exploitation',
            self::ETAPE_LOTS => 'conditionnement_lots',
            self::ETAPE_VALIDATION => 'conditionnement_validation'
    );

	public static $libelles = array(
            self::ETAPE_EXPLOITATION => "Entreprise",
			self::ETAPE_LOTS => "Lots IGP",
            self::ETAPE_VALIDATION => "Validation"
    );

	private static $_instance = null;

	public static function getInstance()
	{
		if(is_null(self::$_instance)) {
			self::$_instance = new ConditionnementEtapes();
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

	public function isEtapeDisabled($etape, $doc) {


		if($etape != self::ETAPE_LOTS && $etape != self::ETAPE_VALIDATION && $doc->isModificative()){
			return true;
		}

		if($etape == self::ETAPE_LOTS) {
			foreach ($doc->getLots() as $lot) {
				return false;
			}
			return true;
		}

        return parent::isEtapeDisabled($etape, $doc);
    }

}

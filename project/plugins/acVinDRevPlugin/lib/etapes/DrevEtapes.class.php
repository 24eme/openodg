<?php
class DrevEtapes extends Etapes
{
	const ETAPE_EXPLOITATION = 'exploitation';
	const ETAPE_DR_DOUANE = 'dr_douane';
	const ETAPE_REVENDICATION_SUPERFICIE = 'revendication_superficie';
	const ETAPE_VCI = 'vci';
	const ETAPE_REVENDICATION = 'revendication';
	const ETAPE_DEGUSTATION = 'degustation_conseil';
	const ETAPE_CONTROLE = 'controle_externe';
	const ETAPE_VALIDATION = 'validation';

	public static $etapes = array(
            self::ETAPE_EXPLOITATION => 1,
            self::ETAPE_DR_DOUANE => 2,
			self::ETAPE_REVENDICATION_SUPERFICIE => 3,
            self::ETAPE_VCI => 4,
			self::ETAPE_REVENDICATION => 5,
            self::ETAPE_DEGUSTATION => 6,
            self::ETAPE_CONTROLE => 7,
            self::ETAPE_VALIDATION => 8
    );

	public static $links = array(
            self::ETAPE_EXPLOITATION => 'drev_exploitation',
            self::ETAPE_DR_DOUANE => 'drev_scrape_dr',
			self::ETAPE_REVENDICATION_SUPERFICIE => 'drev_revendication_superficie',
            self::ETAPE_VCI => 'drev_vci',
			self::ETAPE_REVENDICATION => 'drev_revendication',
            self::ETAPE_DEGUSTATION => 'drev_degustation_conseil',
            self::ETAPE_CONTROLE => 'drev_controle_externe',
            self::ETAPE_VALIDATION => 'drev_validation'
    );

	public static $libelles = array(
            self::ETAPE_EXPLOITATION => "Exploitation",
            self::ETAPE_DR_DOUANE => "Déclaration de récolte",
            self::ETAPE_REVENDICATION_SUPERFICIE => "Revendication des superficies",
            self::ETAPE_VCI => "Répartition du VCI %campagne%",
			self::ETAPE_REVENDICATION => "Revendication",
            self::ETAPE_DEGUSTATION => "Dégustation<br/>conseil",
            self::ETAPE_CONTROLE => "Contrôle<br/>externe",
            self::ETAPE_VALIDATION => "Validation"
    );

	private static $_instance = null;

	public static function getInstance()
	{
		if(is_null(self::$_instance)) {
			self::$_instance = new DrevEtapes();
		}
		return self::$_instance;
	}

	protected function filterItems($items) {
		if(!DRevConfiguration::getInstance()->hasPrelevements()) {
			unset($items[self::ETAPE_DEGUSTATION]);
			unset($items[self::ETAPE_CONTROLE]);
		}

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

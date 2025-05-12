<?php
class DrevEtapes extends Etapes
{
	const ETAPE_EXPLOITATION = 'exploitation';
	const ETAPE_DR_DOUANE = 'dr_douane';
	const ETAPE_REVENDICATION_SUPERFICIE = 'revendication_superficie';
	const ETAPE_VCI = 'vci';
	const ETAPE_LOTS = 'lots';
	const ETAPE_REVENDICATION = 'revendication';
	const ETAPE_VALIDATION = 'validation';

	public static $etapes = array(
            self::ETAPE_EXPLOITATION => 1,
            self::ETAPE_DR_DOUANE => 2,
			self::ETAPE_REVENDICATION_SUPERFICIE => 3,
            self::ETAPE_VCI => 4,
			self::ETAPE_LOTS => 5,
			self::ETAPE_REVENDICATION => 6,
            self::ETAPE_VALIDATION => 7
    );

	public static $links = array(
            self::ETAPE_EXPLOITATION => 'drev_exploitation',
            self::ETAPE_DR_DOUANE => 'drev_dr',
			self::ETAPE_REVENDICATION_SUPERFICIE => 'drev_revendication_superficie',
            self::ETAPE_VCI => 'drev_vci',
            self::ETAPE_LOTS => 'drev_lots',
			self::ETAPE_REVENDICATION => 'drev_revendication',
            self::ETAPE_VALIDATION => 'drev_validation'
    );

	public static $libelles = array(
            self::ETAPE_EXPLOITATION => "Entreprise",
            self::ETAPE_DR_DOUANE => "Document douanier",
            self::ETAPE_REVENDICATION_SUPERFICIE => "Superficies",
            self::ETAPE_VCI => "Répartition du VCI %campagne%",
			self::ETAPE_LOTS => "Lots IGP",
			self::ETAPE_REVENDICATION => "Volumes AOP",
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
		if(!ConfigurationClient::getCurrent()->declaration->isRevendicationParLots()) {
			unset($items[self::ETAPE_LOTS]);
		}

		if(!ConfigurationClient::getCurrent()->declaration->isRevendicationAOC()) {
			unset($items[self::ETAPE_REVENDICATION_SUPERFICIE]);
			unset($items[self::ETAPE_VCI]);
			unset($items[self::ETAPE_REVENDICATION]);
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

	public function getLibelle($step, $doc = null) {

		if($step == self::ETAPE_DR_DOUANE) {

			return $doc->getDocumentDouanierType();
		}

		return parent::getLibelle($step, $doc);
    }

    public function getLibellesHash()
    {

		return $this->filterItems(self::$libelles);
    }

	public function isEtapeDisabled($etape, $doc) {

		if($etape == self::ETAPE_VCI && !count($doc->getProduitsVci())) {

			return true;
		}

        if( ! DRevConfiguration::getInstance()->hasEtapesAOC() && ($etape != self::ETAPE_LOTS) && ($etape != self::ETAPE_VALIDATION) && $doc->isModificative()){
			return true;
		}

		if($etape == self::ETAPE_LOTS) {
			if (count($doc->getProduitsLots())) {
				return false;
			}
			foreach ($doc->getLots() as $lot) {
				return false;
			}
			return true;
		}

		if($etape == self::ETAPE_REVENDICATION && !count($doc->getProduitsWithoutLots())) {

			return true;
		}

        if ($etape === self::ETAPE_REVENDICATION_SUPERFICIE && DrevConfiguration::getInstance()->hasEtapeSuperficie() === false) {
            return true;
        }

        return parent::isEtapeDisabled($etape, $doc);
    }

}

<?php
class AdelpheEtapes extends Etapes
{
	const ETAPE_VOLUME_CONDITIONNE = 'volume_conditionne';
	const ETAPE_REPARTITION_BIB = 'repartition_bib';
	const ETAPE_VALIDATION = 'validation';

	public static $etapes = array(
            self::ETAPE_VOLUME_CONDITIONNE => 1,
            self::ETAPE_REPARTITION_BIB => 2,
            self::ETAPE_VALIDATION => 3
    );

	public static $links = array(
            self::ETAPE_VOLUME_CONDITIONNE => 'adelphe_volume_conditionne',
            self::ETAPE_REPARTITION_BIB => 'adelphe_repartition_bib',
						self::ETAPE_VALIDATION => 'adelphe_validation',
    );

	public static $libelles = array(
            self::ETAPE_VOLUME_CONDITIONNE => "Volume conditionné",
            self::ETAPE_REPARTITION_BIB => "Répartition BIB",
            self::ETAPE_VALIDATION => "Validation"
    );

	private static $_instance = null;

	public static function getInstance() {
		if(is_null(self::$_instance)) {
			self::$_instance = new self();
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

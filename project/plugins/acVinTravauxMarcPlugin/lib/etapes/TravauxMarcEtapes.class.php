<?php
class TravauxMarcEtapes extends Etapes
{
	const ETAPE_EXPLOITATION = 'exploitation';
	const ETAPE_FOURNISSEURS = 'fournisseurs';
	const ETAPE_DISTILLATION = 'distillation';
	const ETAPE_VALIDATION = 'validation';

	private static $_instance = null;

	public static $etapes = array(
            self::ETAPE_EXPLOITATION => 1,
            self::ETAPE_FOURNISSEURS => 2,
            self::ETAPE_DISTILLATION => 3,
            self::ETAPE_VALIDATION => 4,
    );

	public static $links = array(
            self::ETAPE_EXPLOITATION => 'travauxmarc_exploitation',
            self::ETAPE_FOURNISSEURS => 'travauxmarc_fournisseurs',
            self::ETAPE_DISTILLATION => 'travauxmarc_distillation',
            self::ETAPE_VALIDATION => 'travauxmarc_validation',
    );

	public static $libelles = array(
            self::ETAPE_EXPLOITATION => 'Exploitation',
            self::ETAPE_FOURNISSEURS => 'Fournisseurs',
            self::ETAPE_DISTILLATION => 'Distillation',
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

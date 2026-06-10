<?php

class ExportDRaPPDFConfig extends ExportPDFConfig
{
	public function __construct() {
		parent::__construct();
		$this->subject = 'Déclaration de Renonciation à Produire';
		$this->orientation = self::ORIENTATION_LANDSCAPE;
		$this->keywords = 'Teledeclaration, Parcellaire, Parcelle, Parcelles, Irrigable, Declaration';
		$this->creator = 'Syndicat / ODG';
		$this->author = 'Syndicat / ODG';
	}
}

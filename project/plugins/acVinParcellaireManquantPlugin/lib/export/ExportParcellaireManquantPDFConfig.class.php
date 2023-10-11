<?php

class ExportParcellaireManquantPDFConfig extends ExportPDFConfig
{
	public function __construct() {
		parent::__construct();
		$this->subject = 'Identification des parcelles manquantes';
		$this->orientation = self::ORIENTATION_LANDSCAPE;
		$this->keywords = 'Teledeclaration, Parcellaire, Parcelle, Parcelles, Manquants, Declaration';
		$this->creator = 'Syndicat des Côtes de Provence';
		$this->author = 'Syndicat des Côtes de Provence';
	}
}

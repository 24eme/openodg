<?php

class ExportParcellaireAffectationPDFConfig extends ExportPDFConfig
{
	public function __construct() {
		parent::__construct();
		$this->subject = 'Déclaration d\'affectation parcellaire';
		$this->orientation = self::ORIENTATION_LANDSCAPE;
		$this->keywords = 'Teledeclaration, Parcellaire, Parcelle, Parcelles, Affectation, Declaration';
		$this->creator = 'Syndicat des Côtes de Provence';
		$this->author = 'Syndicat des Côtes de Provence';
	}
}

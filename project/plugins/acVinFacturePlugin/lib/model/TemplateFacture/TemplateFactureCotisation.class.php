<?php
/**
 * Model for TemplateFactureCotisation
 *
 */

class TemplateFactureCotisation extends BaseTemplateFactureCotisation {

	public function generateCotisations($document) {
		$cotisationsCollection = $this->getInstanceCotisation($document);

		return $cotisationsCollection->getCotisations();
	}

	public function getInstanceCotisation($document) {
		$modele = $this->modele;

		return new $modele($this, $document);
	}

	public function isForType($types) {
		if(!is_array($types)) {
			$types = [$types];
		}
		foreach($this->details as $detail) {
			if($detail->isForType($types)) {

				return true;
			}
		}

		return false;
	}

	public function isRequired() {

		return $this->exist('required') && $this->get('required');
	}

	public function isDisabled() {

		return $this->exist('disabled') && $this->get('disabled');
	}
}

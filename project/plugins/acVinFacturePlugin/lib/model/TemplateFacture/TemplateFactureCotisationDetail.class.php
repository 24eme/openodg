<?php
/**
 * Model for TemplateFactureCotisationDetail
 *
 */

class TemplateFactureCotisationDetail extends BaseTemplateFactureCotisationDetail {

	public function getInstanceCotisation($document) {
		if(!$this->isForType($document->getType())) {

			return null;
		}
		$modele = $this->modele;

		return new $modele($this, $document);
	}

	public function isForType($type) {

		return in_array($type, $this->docs->toArray(true, false));
	}

}

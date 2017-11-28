<?php
/**
 * Model for TemplateFactureCotisationDetail
 *
 */

class TemplateFactureCotisationDetail extends BaseTemplateFactureCotisationDetail {

	public function getInstanceCotisation($document) {

		$modele = $this->modele;

		return new $modele($this, $document);
	}

}

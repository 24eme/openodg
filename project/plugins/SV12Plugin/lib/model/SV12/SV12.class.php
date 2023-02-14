<?php
/**
 * Model for SV12
 *
 */

class SV12 extends BaseSV12 {

	public function constructId() {
		$this->set('_id', 'SV12-' . $this->identifiant . '-' . $this->campagne);
	}

	public function getConfiguration() {

		return ConfigurationClient::getConfiguration($this->campagne.'-12-10');
	}

    public function isValideeOdg() {
        if (DRConfiguration::getInstance()->hasValidationDR()) {
            return $this->exist('validation_odg') && ($this->validation_odg);
        }
        return false;
    }
}

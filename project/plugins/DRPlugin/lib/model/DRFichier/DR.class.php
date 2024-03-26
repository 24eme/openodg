<?php
/**
 * Model for DR
 *
 */

class DR extends BaseDR {

	public function constructId() {
		$this->set('_id', 'DR-' . $this->identifiant . '-' . $this->campagne);
	}

	public function getConfiguration() {

		return ConfigurationClient::getConfiguration($this->campagne.'-12-10');
	}

    public static function isPieceEditable($admin = false) {
    	return ($admin)? true : false;
    }

    public function save()
    {
        if (DRConfiguration::getInstance()->hasValidationDR()) {
            $this->storeDeclarant();
        }
        foreach($this->donnees as $d) {
            $d->updateTiers();
        }
        parent::save();
    }

    public function isValideeOdg() {
        if (DRConfiguration::getInstance()->hasValidationDR()) {
            return $this->exist('validation_odg') && ($this->validation_odg);
        }
        return false;
    }

    public function hasApporteurs($include_non_reconnu = false) {
        return false;
    }

    public function isExcluExportCsv() {
        if ($this->isBailleur()) {
            return true;
        }
        return false;
    }
}

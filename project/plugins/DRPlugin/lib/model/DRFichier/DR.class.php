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
        $this->storeDeclarant();
        parent::save();
    }
}

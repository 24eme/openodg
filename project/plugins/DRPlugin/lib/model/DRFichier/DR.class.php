<?php
/**
 * Model for DR
 *
 */

class DR extends BaseDR {


	public function constructId() {
		$this->set('_id', 'DR-' . $this->identifiant . '-' . $this->campagne);
	}
}
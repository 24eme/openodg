<?php
/**
 * Model for SV11
 *
 */

class SV11 extends BaseSV11 {

	public function constructId() {
		$this->set('_id', 'SV11-' . $this->identifiant . '-' . $this->campagne);
	}
}
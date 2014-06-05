<?php
/**
 * Model for DRev
 *
 */

class DRev extends BaseDRev {

    public function constructId() {
        $this->set('_id', 'DREV-' . $this->identifiant . '-' . $this->campagne);
    }

}
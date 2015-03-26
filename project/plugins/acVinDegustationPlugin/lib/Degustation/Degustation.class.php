<?php
/**
 * Model for Degustation
 *
 */

class Degustation extends BaseDegustation {
    public function constructId() {
        $this->set('_id', sprintf("%s-%s-%s-%s", DegustationClient::TYPE_COUCHDB, $this->cvi, str_replace("-", "", $this->date_degustation), $this->appellation));
    }
}
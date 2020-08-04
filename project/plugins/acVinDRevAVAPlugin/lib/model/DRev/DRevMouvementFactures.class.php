<?php
/**
 * Model for DRevMouvementFactures
 *
 */

class DRevMouvementFactures extends BaseDRevMouvementFactures {

    public function getMD5Key() {
        $key = $this->template."_".$this->categorie . '_' .$this->type_hash;

        return $key;
    }

}

<?php
/**
 * Model for DRevMarcMouvementFactures
 *
 */

class DRevMarcMouvementFactures extends BaseDRevMarcMouvementFactures {
    public function getMD5Key() {
        $key = $this->template."_".$this->categorie . '_' .$this->type_hash;

        return $key;
    }
}

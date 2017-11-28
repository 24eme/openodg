<?php
/**
 * Model for DRevMarcMouvement
 *
 */

class DRevMarcMouvement extends BaseDRevMarcMouvement {
    public function getMD5Key() {
        $key = $this->template."_".$this->categorie . '_' .$this->type_hash;

        return $key;
    }
}

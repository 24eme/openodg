<?php
/**
 * Model for DRevMouvement
 *
 */

class DRevMouvement extends BaseDRevMouvement {

    public function getMD5Key() {
        $key = $this->template."_".$this->categorie . '_' .$this->type_hash;

        return $key;
    }

}

<?php
/**
 * Model for AbonnementMouvement
 *
 */

class AbonnementMouvement extends BaseAbonnementMouvement {

    public function getMD5Key() {
        $key = $this->template."_".$this->categorie . '_' .$this->type_hash;

        return $key;
    }
    
}

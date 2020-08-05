<?php
/**
 * Model for DRevMouvement
 *
 */

class RegistreVCIMouvementFactures extends BaseRegistreVCIMouvementFactures {

    public function getMD5Key() {
        $key = $this->template."_".$this->categorie . '_' .$this->type_hash;

        return $key;
    }

}

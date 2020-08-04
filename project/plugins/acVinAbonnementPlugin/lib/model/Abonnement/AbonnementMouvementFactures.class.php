<?php
/**
 * Model for AbonnementMouvement
 *
 */

class AbonnementMouvementFactures extends BaseAbonnementMouvementFactures {

    public function getMD5Key() {
        $key = $this->template."_".$this->categorie . '_' .$this->type_hash;

        return $key;
    }

}

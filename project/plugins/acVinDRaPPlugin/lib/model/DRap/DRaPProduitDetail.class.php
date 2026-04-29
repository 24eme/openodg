<?php
/**
 * Model for DRaPProduitDetail
 *
 */

class DRaPProduitDetail extends BaseDRaPProduitDetail {

    public function setSuperficie($value)
    {
        if (! $value) {    /*La gestion des valeurs supérieures a la superficie du parcellaire sont géré a un endroit que je n'ai pas trouvé */
            return $this->_set('superficie', $this->superficie_parcellaire);
        }
        return $this->_set('superficie', $value);
    }
}

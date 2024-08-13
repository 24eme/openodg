<?php
/**
 * Model for ParcellaireIntentionAffectationProduitDetail
 *
 */

class ParcellaireIntentionAffectationProduitDetail extends ParcellaireAffectationProduitDetail {

    public function configureTree() {
        $this->_root_class_name = 'ParcellaireIntentionAffectation';
        $this->_tree_class_name = 'ParcellaireIntentionAffectationProduitDetail';
    }

    public function updateFromParcellaire() {
        $p = $this->getDocument()->getParcelleFromParcellaire($this->getParcelleId());
    }

}

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
        if ($this->exist('superficie_affectation')) {
            $this->superficie = $this->superficie_affectation;
            $this->remove('superficie_affectation');
        }
        ParcellaireClient::CopyParcelle($this, $p);
    }

    public function getProduitHash() {
        return $this->getParent()->getParent()->getHash();
    }

}

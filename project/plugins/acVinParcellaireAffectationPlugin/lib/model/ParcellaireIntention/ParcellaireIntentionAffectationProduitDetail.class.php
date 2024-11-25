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
        if (!$p) {
            $this->delete();
            return;
        }
        if ($this->exist('superficie_affectation')) {
            $this->superficie = $this->superficie_affectation;
            $this->remove('superficie_affectation');
        }
        ParcellaireClient::CopyParcelle($this, $p, false);
    }

    public function getProduitHash() {
        return $this->getParent()->getParent()->getHash();
    }

    public function getSuperficie($destinataireIdentifiant = null) {
        $s = $this->_get('superficie');
        if (!$s && $this->affectation) {
            $s = $this->superficie_parcellaire;
        }
        return $s;
    }

}

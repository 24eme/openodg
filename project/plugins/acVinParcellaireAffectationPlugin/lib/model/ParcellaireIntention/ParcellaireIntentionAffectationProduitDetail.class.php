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

    public function getParcelleId() {
        if (!$this->_get('parcelle_id')) {
            $p = null;
            if ($this->getDocument()->getParcellaire()) {
                $p = ParcellaireClient::getInstance()->findParcelle($this->getDocument()->getParcellaire(), $this, 0);
            }
            if (!$p) {
                throw new sfException('no parcelle id found for '.$this->getHash());
            }
            $this->_set('parcelle_id', $p->getParcelleId());
        }
        return $this->_get('parcelle_id');
    }
}

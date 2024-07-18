<?php
/**
 * Model for ParcellaireIrrigueProduitDetail
 *
 */

class ParcellaireIrrigueProduitDetail extends BaseParcellaireIrrigueProduitDetail {

    public function getProduit() {

        return $this->getParent()->getParent();
    }

    public function getProduitLibelle() {

        return $this->getProduit()->getLibelle();
    }

    public function getLieuLibelle() {
        if ($this->lieu) {

            return $this->lieu;
        }

        return $this->getLieuNode()->getLibelle();
    }
    
    public function getCepageLibelle() {

        return $this->getCepage();
    }

    public function getLieuNode() {

        return $this->getProduit()->getConfig()->getLieu();
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

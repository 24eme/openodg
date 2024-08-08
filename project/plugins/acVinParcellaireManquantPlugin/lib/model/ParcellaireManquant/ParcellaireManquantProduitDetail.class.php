<?php
/**
 * Model for ParcellaireManquantProduitDetail
 *
 */

class ParcellaireManquantProduitDetail extends BaseParcellaireManquantProduitDetail {

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

    public function setPourcentage($pourcentage)
    {
        $this->_set('pourcentage', round($pourcentage, 2));
    }

    public function getParcelleParcellaire() {
        $parc = $this->getDocument()->getParcellaire();
        if ($id = $this->_get('parcelle_id')) {
            return $parc->getParcelleFromParcellaireId($id);
        }
        $p = ParcellaireClient::findParcelle($parc, $this);
        $this->set('parcelle_id', $p->parcelle_id);
        return $p;
    }

    public function getSuperficieParcellaire() {
        return $this->superficie;
    }

    public function getParcelleId() {
        if ($i = $this->_get('parcelle_id')) {
            return $i;
        }
        $p = $this->getParcelleParcellaire();
        if (!$p) {
            return null;
        }
        $id = $p->getParcelleId();
        $this->set('parcelle_id', $id);
        return $id;
    }
}

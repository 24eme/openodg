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
}

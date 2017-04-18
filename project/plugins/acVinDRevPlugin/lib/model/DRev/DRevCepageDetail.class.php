<?php
/**
 * Model for DRevCepageDetail
 *
 */

class DRevCepageDetail extends BaseDRevCepageDetail {

    public function getConfig() {

        return $this->getCepage()->getConfig();
    }

    public function getProduitsCepage()
    {

        return array($this->getHash() => $this);
    }

    public function getCepage() {

        return $this->getParent()->getParent();
    }

    public function getCouleur() {

        return $this->getCepage()->getCouleur();
    }

    public function getLieuNode() {

        return $this->getCouleur()->getLieu();
    }

    public function getAppellation() {

        return $this->getLieuNode()->getAppellation();
    }

    public function resetRevendique() {
        $this->superficie_revendique_total = null;
        $this->superficie_revendique = null;
        $this->superficie_revendique_vt = null;
        $this->superficie_revendique_sgn = null;
        $this->volume_revendique_total = null;
        $this->volume_revendique = null;
        $this->volume_revendique_vt = null;
        $this->volume_revendique_sgn = null;
        if($this->canHaveSuperficieVinifiee()) {
            $this->superficie_vinifiee_total = null;
            $this->superficie_vinifiee = null;
            $this->superficie_vinifiee_total_vt = null;
            $this->superficie_vinifiee_sgn = null;
        }
    }

    public function hasVtsgn() {

        return $this->volume_revendique_vt || $this->volume_revendique_sgn;
    }

    public function getProduitHash() {

        return $this->getCepage()->getProduitHash();
    }

    public function updateTotal() {
        $this->volume_revendique_total = round($this->volume_revendique + $this->volume_revendique_sgn + $this->volume_revendique_vt, 2);
        $this->superficie_revendique_total = round($this->superficie_revendique + $this->superficie_revendique_sgn + $this->superficie_revendique_vt, 2);
        if($this->canHaveSuperficieVinifiee()) {
            $this->superficie_vinifiee_total = round($this->superficie_vinifiee + $this->superficie_vinifiee_sgn + $this->superficie_vinifiee_vt, 2);
        }
    }

    public function isCleanable() {
        $this->updateTotal();

        return !$this->volume_revendique_total && !$this->superficie_revendique_total;
    }

    public function cleanNode() {

        return false;
    }

    public function getLieuLibelle() {
        if ($this->lieu) {

            return $this->lieu;
        }

        return $this->getLieuNode()->getLibelle();
    }

    public function getCepageLibelle() {

        return $this->getCepage()->getLibelle();
    }

    public function getLibelle() {
        if(!$this->_get('libelle')) {
            $cepage_libelle = sprintf("%s", $this->getCepage()->getLibelle());

            if($this->lieu) {
                $cepage_libelle = sprintf("%s - %s", $this->getCepage()->getLibelle(), $this->lieu);
            }
            $this->_set('libelle', sprintf("%s", $cepage_libelle));

            if($this->getLieuNode()->getLibelle()) {
                $this->_set('libelle', sprintf("%s - %s", $this->getLieuNode()->getLibelle(), $cepage_libelle));
            }
        }

        return $this->_get('libelle');
    }

    public function getProduitLibelleComplet() {

        return trim($this->getAppellation()->getLibelleComplet().' '.$this->getLieuLibelle());
    }

    public function canHaveSuperficieVinifiee() {
    	return ($this->exist('superficie_vinifiee'));
    }

}

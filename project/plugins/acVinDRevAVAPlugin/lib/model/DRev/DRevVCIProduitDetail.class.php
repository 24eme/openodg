<?php
/**
 * Model for DRevVCIProduitDetail
 *
 */

class DRevVCIProduitDetail extends BaseDRevVCIProduitDetail {

    public function getLibelleComplet() {
      if (count($this->getParent()) > 1) {
         return $this->getLibelleProduit().' - '.$this->getLibelle();
      }
      return $this->getLibelleProduit();
    }

    public function getLibelleProduit() {
      return $this->getParent()->getParent()->getLibelleComplet();
    }
    public function getLibelle() {
      return $this->stockage_libelle;
    }

    public function getCouleur() {
		return $this->getParent()->getParent()->getCouleur();
    }

}

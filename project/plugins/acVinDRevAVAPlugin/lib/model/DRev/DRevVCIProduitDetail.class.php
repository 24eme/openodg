<?php
/**
 * Model for DRevVCIProduitDetail
 *
 */

class DRevVCIProduitDetail extends BaseDRevVCIProduitDetail {

    public function isStockageCaveParticuliere() {

        return $this->getKey() == RegistreVCIClient::LIEU_CAVEPARTICULIERE || $this->getKey() == $this->getDocument()->identifiant;
    }

    public function isStockageNegociant() {

        return $this->getDocument()->isRecoltant() && !$this->isStockageCaveParticuliere() && $this->getStockageEtablissement()->hasFamille(EtablissementClient::FAMILLE_NEGOCIANT);
    }

    public function getStockageEtablissement() {
        if($this->isStockageCaveParticuliere()) {

            return $this->getDocument()->getEtablissementObject();
        }

        return EtablissementClient::getInstance()->find('ETABLISSEMENT-'.$this->stockage_identifiant);
    }

    public function getLibelleComplet() {
      return $this->getLibelleProduit();
    }

    public function getLibelleProduit() {
      return ($this->getParent()->getParent()->getLibelle())? $this->getParent()->getParent()->getLibelleComplet().' '. $this->getParent()->getParent()->getLibelle() :  $this->getParent()->getParent()->getLibelleComplet();
    }
    public function getLibelle() {
      return $this->stockage_libelle;
    }

    public function getCouleur() {
		return $this->getParent()->getParent()->getCouleur();
    }

    public function getTotalVolumeUtilise() {
    	return round($this->destruction + $this->complement + $this->substitution + $this->rafraichi, 2);
    }

    public function getTotalStockDebut() {
    	return round($this->stock_precedent + $this->constitue, 2);
    }

    public function getStockFinalCalcule() {
    	return round($this->getTotalStockDebut() - $this->getTotalVolumeUtilise(), 2);
    }

    public function updateStock() {
    	$this->stock_final = $this->getStockFinalCalcule();
    }

}

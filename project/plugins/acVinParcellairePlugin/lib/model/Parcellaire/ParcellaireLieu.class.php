<?php
/**
 * Model for ParcellaireLieu
 *
 */

class ParcellaireLieu extends BaseParcellaireLieu {
	public function getMention() 
    {
        return $this->getParent();
    }

    public function getAppellation() 
    {
        return $this->getMention()->getParent();
    }

    public function getChildrenNode() 
    {
        return $this->getCouleurs();
    }

    public function getProduits($onlyActive = false) 
    {

        return parent::getProduits($onlyActive);
    }

    public function getCouleurs() 
    {
        return $this->filter('^couleur');
    }
    
    public function getAcheteurs() {
        $acheteursArray = array();
        foreach ($this->getDocument()->getAcheteurs() as $cvi => $acheteur) {
            foreach ($acheteur->produits as $hashKey => $produitLieu) {
                if($produitLieu->hash_produit == $this->getHash()){
                    $acheteursArray[$cvi] = $acheteur;
                }
            }
        }
        return $acheteursArray;
    }
}
<?php

class DRevAppellation extends BaseDRevAppellation 
{
    public function getConfigProduits() {

        return $this->getConfig()->getProduitsFilter(_ConfigurationDeclaration::TYPE_DECLARATION_DREV_REVENDICATION_CEPAGE);
    }
    
	public function getGenre()
    {
        return $this->getParent();
    }
    
    public function getChildrenNode() 
    {
        return $this->getMentions();
    }

    public function getNextSisterActive() {
        if(!$this->getNextSister()) {

            return null;
        }

        if(!$this->getNextSister()->isActive()) {

            return $this->getNextSister()->getNextSisterActive();
        }

        return $this->getNextSister();
    }

    public function isActive() {
        foreach($this->getProduits() as $produit) {
            if($produit->isActive()) {
                return true;
            }            
        }

        return false;
    }

    public function getMentions()
    {
        return $this->filter('^mention');
    }

    public function getLieux() 
    {  
        return $this->mention->getLieux();
    }
    
    public function getLibelleComplet() 
    {
        return $this->libelle;
    }
    
}

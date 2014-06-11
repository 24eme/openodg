<?php

abstract class _DRevDeclarationNoeud extends acCouchdbDocumentTree {

    protected $total_superficie_before;
    protected $total_volume_before;

    public function getConfig() 
    {
        return $this->getCouchdbDocument()->getConfiguration()->get($this->getHash());
    }

    abstract public function getChildrenNode();
    
	public function getChildrenNodeDeep($level = 1) 
	{
      if($this->getConfig()->hasManyNoeuds()) {
          
          throw new sfException("getChildrenNodeDeep() peut uniquement être appelé d'un noeud qui contient un seul enfant...");
      }

      $node = $this->getChildrenNode()->getFirst();
      
      if($level > 1) {
        
        return $node->getChildrenNodeDeep($level - 1);
      }

      return $node->getChildrenNode();
    }

    public function getProduits($onlyActive = false) 
    {
        $produits = array();
        foreach($this->getChildrenNode() as $key => $item) {
            $produits = array_merge($produits, $item->getProduits($onlyActive));
        }

        return $produits;
    }
    
    public function getLibelle() {
        if(is_null($this->_get('libelle'))) {
            $this->_set('libelle', $this->getConfig()->getLibelle());
        }

        return $this->_get('libelle');
    }
    
    public function getLibelleComplet() 
    {
    	$libelle = $this->getParent()->getLibelleComplet();
    	return trim($libelle).' '.$this->libelle;
    }
    
	public function getTotalTotalSuperficie()
    {
    	$total = 0;
        foreach($this->getChildrenNode() as $key => $item) {
            $total += $item->getTotalSuperficie();
        }
        return $total;
    }
    
	public function getTotalVolumeRevendique()
    {
    	$total = 0;
        foreach($this->getChildrenNode() as $key => $item) {
            $total += $item->getTotalSuperficie();
        }
        return $total;
    }


} 
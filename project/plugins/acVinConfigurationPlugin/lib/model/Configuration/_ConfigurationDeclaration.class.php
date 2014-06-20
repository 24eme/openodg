<?php

abstract class _ConfigurationDeclaration extends acCouchdbDocumentTree {

    protected $produits = null;
    protected $drev_produits = null;
    protected $drev_lot_produits = null;
    protected $produits_filter = array(self::TYPE_DECLARATION_DR => null, self::TYPE_DECLARATION_DS => null);

    const TYPE_DECLARATION_DR = 'DR';
    const TYPE_DECLARATION_DS = 'DS';

    protected function loadAllData() {
      parent::loadAllData();
      $this->getProduits();
      $this->getDrevProduits();
      $this->getProduitsFilter(self::TYPE_DECLARATION_DR);
      $this->getProduitsFilter(self::TYPE_DECLARATION_DS);
      $this->getRendementAppellation();
      $this->getRendementCouleur();
      $this->getRendementCepage();
      $this->existRendementAppellation();
      $this->existRendementCouleur();
      $this->existRendementCepage();
      $this->getChildrenFilter(self::TYPE_DECLARATION_DR);
      $this->getChildrenFilter(self::TYPE_DECLARATION_DS);
    }

    abstract public function getChildrenNode();

    public function getChildrenNodeArray() {
        $items = array();
        foreach($this->getChildrenNode() as $item) {
            $items[$item->getKey()] = $item;
        }

        return $items;
    }

    public function getChildrenFilter($type_declaration = null) {
      
      return $this->store('get_children_filter_'.$type_declaration, array($this, 'getChildrenFilterStorable'), array($type_declaration));
    }

    public function getChildrenFilterStorable($type_declaration = null) {
      $children = array();
      foreach($this->getChildrenNode() as $item) {
        if($type_declaration == self::TYPE_DECLARATION_DR && !$item->isForDR()) {
          continue;
        }

        if($type_declaration == self::TYPE_DECLARATION_DS && !$item->isForDS()) {
          continue;
        }

        $children[$item->getKey()] = $item;
      }

      return $children;
    }

    public function getLibelleLong() {
      if($this->exist('libelle_long') && $this->_get('libelle_long')) {

        return $this->_get('libelle_long');
      }

      return $this->getLibelle();
    }

    public function getParentNode() {
      if ($this->getKey() == 'recolte') {
 
        throw new sfException('Noeud racine atteint');
      }

      return $this->getParent();
    }

    public function getChildrenNodeDeep($level = 1) {
      if($this->hasManyNoeuds()) {
          
          throw new sfException("getChildrenNodeDeep() peut uniquement être appelé d'un noeud qui contient un seul enfant...");
      }

      $node = $this->getChildrenNode()->getFirst();
      
      if($level > 1) {
        
        return $node->getChildrenNodeDeep($level - 1);
      }

      return $node->getChildrenNode();
    }

    public function hasManyNoeuds(){
        if(count($this->getChildrenNode()) > 1){
            return true;
        }
        return false;
    }

    public function getProduits() {
      if(is_null($this->produits)) {
        $this->produits = array();
        foreach($this->getChildrenNode() as $key => $item) {
            $this->produits = array_merge($this->produits, $item->getProduits());
        }
      }

      return $this->produits;
    }
    
	public function getDrevProduits()
    {
      if(is_null($this->drev_produits)) {
        $this->drev_produits = array();
        foreach($this->getChildrenNode() as $key => $item) {
            $this->drev_produits = array_merge($this->drev_produits, $item->getDrevProduits());
        }
      }

      return $this->drev_produits;
    }
    
	public function getDrevLotProduits()
    {
        $drev_lot_produits = array();
        foreach($this->getChildrenNode() as $key => $item) {
            $drev_lot_produits = array_merge($drev_lot_produits, $item->getDrevLotProduits());
        }
      	return $drev_lot_produits;
    }

    public function getProduitsFilter($type_declaration = null) {
      if(!$type_declaration) {

        return $this->getProduits();
      }
      
      if(is_null($this->produits[$type_declaration])) {
        $this->produits[$type_declaration] = array();
        foreach($this->getChildrenFilter($type_declaration) as $key => $item) {
            $this->produits[$type_declaration] = array_merge($this->produits[$type_declaration], $item->getProduitsFilter($type_declaration));
        }
      }

      return $this->produits[$type_declaration];
    }

    /**** RENDEMENT POUR LA DR ****/

    public function getRendement() {

      return $this->getRendementCepage();
    }

    public function getRendementNoeud() {

        return -1;
    }

    public function getRendementAppellation() {
      
        return $this->getRendementByKey('rendement_appellation');
    }

    public function getRendementCouleur() {
        
        return $this->getRendementByKey('rendement_couleur');
    }

    public function getRendementCepage() {
        
        return $this->getRendementByKey('rendement');
    }

    public function hasRendementAppellation() {
        
        return $this->hasRendementByKey('rendement_appellation');
    }

    public function hasRendementCouleur() {
        
        return $this->hasRendementByKey('rendement_couleur');
    }

    public function hasRendementCepage() {
        
        return $this->hasRendementByKey('rendement');
    }

    public function existRendementAppellation() {
        
        return $this->existRendementByKey('rendement_appellation');
    }

    public function existRendementCouleur() {
        
        return $this->existRendementByKey('rendement_couleur');
    }

    public function existRendementCepage() {
        
        return $this->existRendementByKey('rendement');
    }

    public function existRendement() {

        return $this->existRendementCepage() || $this->existRendementCouleur() || $this->existRendementAppellation();
    }

    public function hasRendementNoeud() {
        $r = $this->getRendementNoeud();

        return ($r && $r > 0);
    }

    public function existRendementByKey($key) {
        
        return $this->store('exist_rendement_by_key_'.$key, array($this, 'existRendementByKeyStorable'), array($key));
    }

    protected function existRendementByKeyStorable($key) {
      if($this->hasRendementByKey($key)) {

        return true;
      }

      foreach($this->getChildrenNode() as $noeud) {
        if($noeud->existRendementByKey($key)) {

          return true;
        }
      }

      return false;
    }

    protected function getRendementByKey($key) {
      
        return $this->store('rendement_by_key_'.$key, array($this, 'findRendementByKeyStorable'), array($key));
    }

    protected function findRendementByKeyStorable($key) {
        if ($this->exist($key) && $this->_get($key)) {

            return $this->_get($key);
        }

        return $this->getParentNode()->get($key);
    }

    protected function hasRendementByKey($key) {
        $r = $this->getRendementByKey($key);

        return ($r && $r > 0);
    }


    /**** FIN DU RENDEMENT POUR LA DR ****/

    public function hasMout() {
        if ($this->exist('mout')) {
            
            return ($this->mout == 1);
        } 

        return $this->getParentNode()->hasMout();
    }
    
    public function excludeTotal() 
    {
        return ($this->exist('exclude_total') && $this->get('exclude_total'));
    }

    public function hasTotalCepage() {
      
      return $this->store('has_total_cepage', array($this, 'hasTotalCepageStorable'));
    }

    protected function hasTotalCepageStorable() {

      if ($this->exist('no_total_cepage')) {
        
          return !($this->no_total_cepage == 1);
      }

      if ($this->exist('min_quantite') && $this->get('min_quantite')) {
          
          return false;
      } 

      return $this->getParentNode()->hasTotalCepage(); 
    }

    public function hasVtsgn() {
        if ($this->exist('no_vtsgn')) {
            return (! $this->get('no_vtsgn'));
        }


        if ($this->exist('min_quantite') && $this->get('min_quantite')) {
            return false;
        }

        return $this->getParentNode()->hasVtsgn();
    }

    public function isForDR() {
      
        return !$this->exist('no_dr') || !$this->get('no_dr');
    }

    public function isForDS() {

        return !$this->exist('no_ds') || !$this->get('no_ds');
    }
    
    public function isAutoDs() {
        if ($this->exist('auto_ds')) {
            return $this->get('auto_ds');
        }
        
        return $this->getParentNode()->isAutoDs();
    }

}

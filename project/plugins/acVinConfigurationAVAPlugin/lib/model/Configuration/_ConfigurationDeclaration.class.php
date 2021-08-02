<?php

abstract class _ConfigurationDeclaration extends acCouchdbDocumentTree {

    protected $produits = array();
    protected $drev_produits = null;
    protected $drev_lot_produits = null;
    protected $produits_filter = array(self::TYPE_DECLARATION_DR => null, self::TYPE_DECLARATION_DS => null);

    const TYPE_DECLARATION_DR = 'DR';
    const TYPE_DECLARATION_DS = 'DS';
    const TYPE_DECLARATION_DREV_REVENDICATION = 'DREV_REVENDICATION';
    const TYPE_DECLARATION_DREV_REVENDICATION_CEPAGE = 'DREV_REVENDICATION_CEPAGE';
    const TYPE_DECLARATION_DREV_LOTS = 'DREV_LOTS';
    const TYPE_DECLARATION_PARCELLAIRE = 'PARCELLAIRE';
    const TYPE_DECLARATION_DEGUSTATION = 'DEGUSTATION';
    const TYPE_DECLARATION_TIRAGE = 'TIRAGE';

    protected function loadAllData() {
      parent::loadAllData();
      $this->getProduits();
      $this->getProduitsFilter(self::TYPE_DECLARATION_DR);
      $this->getProduitsFilter(self::TYPE_DECLARATION_DS);
      $this->getProduitsFilter(self::TYPE_DECLARATION_DREV_REVENDICATION, "ConfigurationCouleur");
      $this->getProduitsFilter(self::TYPE_DECLARATION_DREV_REVENDICATION_CEPAGE);
      $this->getProduitsFilter(self::TYPE_DECLARATION_DREV_LOTS);
      $this->getProduitsFilter(self::TYPE_DECLARATION_PARCELLAIRE);
      $this->getProduitsFilter(self::TYPE_DECLARATION_DEGUSTATION);
      $this->getProduitsFilter(self::TYPE_DECLARATION_TIRAGE);
      $this->getRendementAppellation();
      $this->getRendementCouleur();
      $this->getRendementCepage();
      $this->existRendementAppellation();
      $this->existRendementCouleur();
      $this->existRendementCepage();
      $this->getChildrenFilter(self::TYPE_DECLARATION_DR);
      $this->getChildrenFilter(self::TYPE_DECLARATION_DS);
      $this->getChildrenFilter(self::TYPE_DECLARATION_DREV_REVENDICATION);
      $this->getChildrenFilter(self::TYPE_DECLARATION_DREV_REVENDICATION_CEPAGE);
      $this->getChildrenFilter(self::TYPE_DECLARATION_DREV_LOTS);
      $this->getChildrenFilter(self::TYPE_DECLARATION_PARCELLAIRE);
      $this->getChildrenFilter(self::TYPE_DECLARATION_DEGUSTATION);
      $this->getChildrenFilter(self::TYPE_DECLARATION_TIRAGE);
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
        if(!$item->hasAcces($type_declaration)) {

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
      if(!array_key_exists("ConfigurationCepage", $this->produits)) {
        $this->produits["ConfigurationCepage"] = array();
        foreach($this->getChildrenNode() as $key => $item) {
            $this->produits["ConfigurationCepage"] = array_merge($this->produits["ConfigurationCepage"], $item->getProduits());
        }
      }

      return $this->produits["ConfigurationCepage"];
    }

    public function getKeyRelation($key) {
        if($this->exist('relations') && $this->relations->exist($key)) {

            return $this->relations->get($key);
        }

        return $this->getKey();
    }

    public function getHashRelation($key) {

        return $this->getParent()->getHashRelation($key)."/".$this->getKeyRelation($key);
    }

    public function getNodeRelation($key) {

        return $this->getDocument()->get($this->getHashRelation($key));
    }

    public function getProduitsFilter($type_declaration = null, $class_node = null) {
      if($class_node && $this instanceof $class_node) {
        return array($this->getHash() => $this);
      }

      if(!$type_declaration) {
        return $this->getProduits();
      }

      if(!isset($this->produits[$type_declaration]) || is_null($this->produits[$type_declaration])) {
        $this->produits[$type_declaration.$class_node] = array();
        foreach($this->getChildrenFilter($type_declaration) as $key => $item) {
            $this->produits[$type_declaration.$class_node] = array_merge($this->produits[$type_declaration.$class_node], $item->getProduitsFilter($type_declaration, $class_node));
        }
      }

      return $this->produits[$type_declaration.$class_node];
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

    public function hasProduitsVtsgn() {
        foreach($this->getProduits() as $produit) {
            if($produit->hasVtsgn()) {

                return true;
            }
        }

        return false;
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

    public function hasAcces($type_declaration) {
        if($type_declaration == self::TYPE_DECLARATION_DR && !$item->isForDR()) {

            return false;
        }

        if($type_declaration == self::TYPE_DECLARATION_DS && !$item->isForDS()) {

            return false;
        }

        if(!$this->exist('no_acces')) {

           return true;
        }

        return (!$this->no_acces->exist($type_declaration) || !$this->no_acces->get($type_declaration));
    }

    public function isAutoDs() {
        if ($this->exist('auto_ds')) {
            return $this->get('auto_ds');
        }

        return $this->getParentNode()->isAutoDs();
    }

    public function isAutoDRev() {
        if ($this->exist('auto_drev')) {
            return $this->get('auto_drev');
        }

        return $this->getParentNode()->isAutoDRev();
    }

    public function getLibelleComplet($libelle_long = false)
    {
    	$libelle = $this->getParent()->getLibelleComplet();

      if($libelle_long) {

          return trim(trim($libelle).' '.$this->getLibelleLong());
      }

    	return trim(trim($libelle).' '.$this->libelle);
    }

    public function existRendementVci() {

    	return $this->existRendementByKey('rendement_vci');
    }

    public function hasRendementVci() {

    	return $this->hasRendementByKey('rendement_vci');
    }

    public function hasRendementVciTotal() {

    	return $this->hasRendementByKey('rendement_vci_total');
    }

    public function getRendementVci() {
    	return $this->getRendementByKey('rendement_vci');
    }

    public function getRendementVciTotal() {
        return $this->getRendementByKey('rendement_vci_total');
    }

    public function getCepagesAutorises()
    {
        return array();
    }

    public function isCepageAutorise($cepage) {

        return in_array($cepage, $this->getCepagesAutorises());
    }

}

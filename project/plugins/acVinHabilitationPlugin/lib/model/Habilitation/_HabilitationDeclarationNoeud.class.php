<?php

abstract class _HabilitationDeclarationNoeud extends acCouchdbDocumentTree {


    public function getConfig()
    {
        return $this->getCouchdbDocument()->getConfiguration()->get($this->getHash());
    }

    public function getConfigChidrenNode() {

        return $this->getConfig()->getChildrenFilter(_ConfigurationDeclaration::TYPE_DECLARATION_DREV_REVENDICATION);
    }

    abstract public function getChildrenNode();

    public function hasManyNoeuds(){
        if(count($this->getChildrenNode()) > 1){
            return true;
        }
        return false;
    }

    public function reorderByConf() {
        $children = array();

        foreach($this->getChildrenNode() as $hash => $child) {
            $children[$hash] = $child->getData();
        }

        foreach($children as $hash => $child) {
            $this->remove($hash);
        }

        foreach($this->getConfig()->getChildrenNode() as $hash => $child) {
            if(!array_key_exists($hash, $children)) {
                continue;
            }

            $child_added = $this->add($hash, $children[$hash]);
            $child_added->reorderByConf();
        }
    }

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

}

<?php

abstract class _DRevDeclarationNoeud extends acCouchdbDocumentTree {

    protected $total_superficie_before;
    protected $total_volume_before;

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

    public function getProduitsVci()
    {
        $produitsVci = array();
        $produits = $this->getProduits();
        foreach($produits as $produit) {
            if(!$produit->hasVci()) {
                continue;
            }
            $produitsVci[$produit->getHash()] = $produit;
        }

        return $produitsVci;
    }

    public function removeVolumeRevendique() {

        foreach($this->getProduits() as $produit) {
            $produit->detail->volume_sur_place = 0;
            $produit->detail->volume_sur_place_revendique = 0;
            $produit->detail->superficie_vinifiee = 0;
            $produit->detail->usages_industriels_sur_place = 0;
            if($produit->exist('detail_vtsgn')) {
                $produit->detail_vtsgn->volume_sur_place = 0;
                $produit->detail_vtsgn->volume_sur_place_revendique = 0;
                $produit->detail_vtsgn->usages_industriels_sur_place = 0;
                if($produit->detail_vtsgn->exist('superficie_vinifiee')) {
                    $produit->detail_vtsgn->superficie_vinifiee = 0;
                }
            }
            $produit->updateRevendiqueFromDetail();
        }

        foreach($this->getProduitsCepage() as $detail) {
            $detail->resetRevendique();
        }

    }

    public function getProduitsCepage()
    {
        $produits = array();
        foreach($this->getChildrenNode() as $key => $item) {
            $produits = array_merge($produits, $item->getProduitsCepage());
        }

        return $produits;
    }

    public function hasVtsgn() {
        foreach($this->getProduits() as $produit) {
            if($produit->canHaveVtsgn() && $produit->volume_revendique_vtsgn) {

                return true;
            }
        }
        foreach($this->getProduitsCepage() as $produit) {
            if($produit->hasVtsgn()) {

                return true;
            }
        }

        return false;
    }

	public function getTotalTotalSuperficie()
    {
    	$total = 0;
        foreach($this->getChildrenNode() as $key => $item) {
            $total += $item->getTotalTotalSuperficie();
        }
        return $total;
    }

	public function getTotalVolumeRevendique()
    {
    	$total = 0;
        foreach($this->getChildrenNode() as $key => $item) {
            $total += $item->getTotalVolumeRevendique();
        }
        return $total;
    }

	public function getTotalSuperficieVinifiee()
    {
    	$total = 0;
        foreach($this->getChildrenNode() as $key => $item) {
            $total += $item->getTotalSuperficieVinifiee();
        }
        return $total;
    }

}

<?php
/**
 * Model for HabilitationCouleur
 *
 */

class HabilitationCouleur extends BaseHabilitationCouleur {

  public function getChildrenNode()
    {
        return $this->getCepages();
    }

    public function getCepages() {

        return $this->filter('^cepage_');
    }

	public function getLieu()
    {
        return $this->getParent();
    }

	public function getMention()
    {
        return $this->getLieu()->getMention();
    }

    public function getAppellation()
    {
    	return $this->getMention()->getAppellation();
    }

  public function getProduitHash() {
    if(strpos($this->getKey(),"_")) {
      $nodes = explode("_",$this->getKey());
      return $this->getLieu()->getProduitHash()."/".$nodes[0]."/".$nodes[1];
    }
    return $this->getHash();
  }

}

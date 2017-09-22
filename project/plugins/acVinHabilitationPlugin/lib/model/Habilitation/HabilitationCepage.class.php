<?php
/**
 * Model for HabilitationCepage
 *
 */

class HabilitationCepage extends BaseHabilitationCepage {

  public function getChildrenNode()
  {
      return $this->details;
  }

  public function getCouleur() {

      return $this->getParent();
  }

  public function getProduitHash() {
      if(strpos($this->getKey(),"_")) {
        $nodes = explode("_",$this->getKey());
        return $this->getCouleur()->getProduitHash()."/".$nodes[0]."/".$nodes[1];
      }
      return $this->getHash();
  }

  public function getOrAddDetailNode()
  {
    $detailsNode = $this->getOrAdd("details");
    $detailsNode->getOrAddDefaultActivities();
  }

  public function getLibelle() {
      if(!$this->_get('libelle')) {
          $this->libelle = $this->getConfig()->getLibelleComplet();
      }

      return $this->_get('libelle');
  }

  public function getLibelleComplet()
  {

      return $this->getLibelle();
  }

  public function getProduits($onlyActive = false)
  {
    if ($onlyActive && !$this->isActive()) {

      return array();
    }

      return array($this->getHash() => $this);
  }

  public function isActive()
  {
    return true;
    // ici regarder si oui on non les details sont remplis
  }

  public function getProduitHashStr(){
    return KeyInflector::slugify($this->getHash());
  }

}

<?php

class ConfigurationCepage extends BaseConfigurationCepage {

    public function getAppellation() {

      return $this->getLieu()->getAppellation();
    }

    public function getLieu() {

        return $this->getCouleur()->getLieu();
    }

    public function getMention() {

        return $this->getLieu()->getMention();
    }

    public function getCouleur() {

        return $this->getParentNode();
    }

    public function getLibelleFormat($labels = array(), $format = "%g% %a% %m% %l% %co% %ce%", $label_separator = ", ") {

        return $this->getLibelle();
    }

    public function getCodeProduit($vtsgn = '') {
        return $this->getDouane()->getFullAppCode($vtsgn).$this->getDouane()->getCodeCepage();
    }
    
    public function existRendementByKey($key) {
        
        return $this->hasRendementByKey($key);
    }
    
  public function getChildrenNode() {
      return null;
  }

  public function getProduits() {
        
    return array($this->getHash() => $this);
  }

  public function getProduitsFilter($type_declaration = null, $class_node = null) {

    return $this->getProduits();
  }

  public function hasLieuEditable() {
        return $this->getParent()->getParent()->getParent()->getParent()->hasLieuEditable();
  }
  
  public function hasDenomination() {
    if ($this->exist('no_denomination')) {
      return !($this->no_denomination == 1);
    } elseif ($this->exist('min_quantite') && $this->get('min_quantite')) {
      return false;
    }
    return true;
  }

  public function hasSuperficie() {
    if ($this->exist('no_superficie')) {
      return !($this->no_superficie == 1);
    } elseif ($this->exist('min_quantite') && $this->get('min_quantite')) {
      return false;
    }
    return true;
  }

  public function isSuperficieRequired() {
    if(!$this->hasSuperficie()) {
      return false;
    }
    
    if ($this->exist('superficie_optionnelle')) {
      return (! $this->get('superficie_optionnelle'));
    }

    return true;
  }

  public function hasOnlyOneDetail() {
    if ($this->exist('only_one_detail') && $this->get('only_one_detail'))
      return true;
    if ($this->exist('min_quantite') && $this->get('min_quantite'))
      return true;
    return false;
  }
  public function hasMinQuantite()
  {
    if ($this->exist('min_quantite') && $this->get('min_quantite'))
      return true;
    return false;
  }

  public function hasMaxQuantite()
  {
    if ($this->exist('max_quantite') && $this->get('max_quantite'))
      return true;
    return false;
  }

  public function hasNoNegociant()
  {
    if ($this->exist('no_negociant') && $this->get('no_negociant'))
      return true;
    return false;
  }

  public function hasNoCooperative()
  {
    if ($this->exist('no_cooperative') && $this->get('no_cooperative'))
      return true;
    return false;
  }

  public function hasNoMout()
  {
    if ($this->exist('no_mout') && $this->get('no_mout'))
      return true;
    return false;
  }

  public function hasNoMotifNonRecolte()
  {
    if ($this->exist('no_motif_non_recolte') && $this->get('no_motif_non_recolte'))
      return true;
    return false;
  }

  public function hasTotalCepage() {
    if (!$this->getLieu()->existRendementCepage()) {
        return false;
    }

    return parent::hasTotalCepage();
  }

  public function getRendementNoeud() {

    return $this->getRendementCepage();
  }

  public function existRendement() {
    if($this->getKey() == 'cepage_RB') {

      return false;
    }

    return parent::existRendement();
  }



    public function getDrevLotProduits()
    {
    	return array($this->getHash());
    }

    public function isAssemblage() {

        return ($this->getKey() == "cepage_ED");
    }

    public function isCepageAutorise($cepage) {
        if($this->isAssemblage()) {

            return $this->getParentNode()->getCepagesAutorises();
        }

        return parent::isCepageAutorise($cepage);
    }

}
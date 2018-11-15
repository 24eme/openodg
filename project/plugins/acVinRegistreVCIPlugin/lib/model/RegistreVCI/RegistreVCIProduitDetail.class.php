<?php
/**
 * Model for RegistreVCIProduitDetail
 *
 */

class RegistreVCIProduitDetail extends BaseRegistreVCIProduitDetail {
    public function addLigne($mouvement_type, $volume) {
      $this->_set($mouvement_type,  $this->{$mouvement_type} + $volume);
      $this->_set('stock_final', $this->stock_final + $volume * RegistreVCIClient::MOUVEMENT_SENS($mouvement_type));
      $this->getParent()->getParent()->addVolume($mouvement_type, $volume);
      return $this;
    }

    public function setConstitue($v) {
      $this->getParent()->getParent()->_set('constitue', $v);
      return $this->_set('constitue', $v);
    }
    public function setRafraichi($v) {
      $this->getParent()->getParent()->_set('rafraichi', $v);
      return $this->_set('rafraichi', $v);
    }
    public function setComplement($v) {
      $this->getParent()->getParent()->_set('complement', $v);
      return $this->_set('complement', $v);
    }
    public function setSubstitue($v) {
      $this->getParent()->getParent()->_set('substitue', $v);
      return $this->_set('substitue', $v);
    }
    public function setDetruit($v) {
      $this->getParent()->getParent()->_set('destruction', $v);
      return $this->_set('destruction', $v);
    }

    public function setStockFinal($v) {
      throw new sfException('Not collable, use addMouvement');
    }

    public function getLibelleComplet() {
      if (count($this->getParent()) > 1) {
         return $this->getLibelleProduit().' - '.$this->getLibelle();
      }
      return $this->getLibelleProduit();
    }

    public function getLibelleProduit() {
      return $this->getParent()->getParent()->getLibelleComplet();
    }
    public function getLibelle() {
      return $this->stockage_libelle;
    }

}

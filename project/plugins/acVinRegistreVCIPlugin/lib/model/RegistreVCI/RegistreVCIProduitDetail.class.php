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

    public function setStockPrecedent($v) {
        if(!is_null($this->stock_final)) {
            throw new Exception("This method can be set only if stock final is not set");
        }
        $this->getParent()->getParent()->_set('stock_precedent', $this->getParent()->getParent()->stock_precedent +  $v);
        $this->getParent()->getParent()->_set('stock_final', $this->getParent()->getParent()->stock_precedent);

        $this->_set('stock_final', $v);
        return $this->_set('stock_precedent', $v);
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

    public function getStockFinal() {
        if(is_null($this->_get('stock_final'))) {

            return $this->_get('stock_final');
        }

        $this->_set('stock_final', round($this->_get('stock_final'), 2));

        return $this->_get('stock_final');
    }

    public function clear() {
        $this->_set('stock_precedent', null);
        $this->_set('destruction', null);
        $this->_set('complement', null);
        $this->_set('substitution', null);
        $this->_set('rafraichi', null);
        $this->_set('constitue', null);
        $this->_set('stock_final', null);
    }

}

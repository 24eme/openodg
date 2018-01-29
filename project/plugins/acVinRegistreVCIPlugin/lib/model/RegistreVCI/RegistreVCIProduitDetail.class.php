<?php
/**
 * Model for RegistreVCIProduitDetail
 *
 */

class RegistreVCIProduitDetail extends BaseRegistreVCIProduitDetail {
    public function addMouvement($mouvement_type, $volume) {
      $this->_set($mouvement_type,  $this->{$mouvement_type} + $volume);
      $this->_set('stock_final', $this->stock_final + $volume * RegistreVCIClient::MOUVEMENT_SENS($mouvement_type));
      $this->getParent()->getParent()->addVolume($mouvement_type, $volume);
      return $this;
    }

    public function setConstitue($v) {
      throw new sfException('Not callable, use addMouvement');
    }
    public function setRafraichi($v) {
      throw new sfException('Not collable, use addMouvement');
    }
    public function setComplement($v) {
      throw new sfException('Not collable, use addMouvement');
    }
    public function setSubstitue($v) {
      throw new sfException('Not collable, use addMouvement');
    }
    public function setDetruit($v) {
      throw new sfException('Not collable, use addMouvement');
    }
    public function setStockFinal($v) {
      throw new sfException('Not collable, use addMouvement');
    }

    public function getLibelle() {
      return $this->stockage_libelle;
    }

}

<?php
/**
 * Model for RegistreVCIProduit
 *
 */

class RegistreVCIProduit extends BaseRegistreVCIProduit {

  public function getConfig()
  {
    return $this->getCouchdbDocument()->getConfiguration()->get($this->getProduitHash());
  }

  public function getLibelle() {
    if(!$this->_get('libelle')) {
      $this->libelle = $this->getConfig()->getLibelleComplet();
      if($this->exist('denomination_complementaire')) {
        $this->libelle .= ' '.$this->denomination_complementaire;
      }
    }

    return $this->_get('libelle');
  }

  public function getLibelleComplet()
  {
    return $this->getLibelle();
  }

  public function getProduitHash() {
      return $this->getHash();
  }

  public function addMouvement($mouvement_type, $volume, $lieu_id) {
    if (!$this->details->exist($lieu_id)) {
      $detail = $this->add('details')->add($lieu_id);
      if ($lieu_id == RegistreVCIClient::LIEU_CAVEPARTICULIERE) {
        $detail->stockage_libelle = "Cave particulière";
      }
      $detail->stockage_identifiant = $lieu_id;
    }
    $detail = $this->details->get($lieu_id);
    $detail->addMouvement($mouvement_type, $volume);
    return $detail;
  }

  public function addVolume($mouvement_type, $volume) {
    $this->_set($mouvement_type, $this->{$mouvement_type} + $volume);
    $this->_set('stock_final', $this->stock_final + $volume * RegistreVCIClient::MOUVEMENT_SENS($mouvement_type));
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



}

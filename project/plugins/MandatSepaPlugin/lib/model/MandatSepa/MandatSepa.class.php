<?php
class MandatSepa extends BaseMandatSepa {

  public function constructId() {
      $id = 'MANDATSEPA-' . $this->creancier->identifiant_rum . '-' . str_replace('-', '', $this->date);
      $this->set('_id', $id);
  }

  public function setDebiteur($debiteur) {
    if (!$debiteur) {
      throw new Exception('Il faut definir un débiteur pour le mandat SEPA.');
    }
    $this->debiteur->setPartieInformations($debiteur);
  }

  public function setCreancier($creancier) {
    if (!$creancier) {
      throw new Exception('Il faut definir un creancier pour le mandat SEPA.');
    }
    $this->creancier->setPartieInformations($creancier);
  }
}

<?php
class MandatSepa extends BaseMandatSepa {

  public function constructId() {
      $id = 'MANDATSEPA-' . $this->debiteur->identifiant_rum . '-' . str_replace('-', '', $this->date);
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

  public function getStatut() {
    return ($this->is_signe)? MandatSepaClient::STATUT_VALIDE : MandatSepaClient::STATUT_NONVALIDE;
  }

  public function switchIsSigne() {
    if ($this->is_signe) {
      $this->is_signe = 0;
      $this->is_actif = 0;
    } else {
      $this->is_signe = 1;
    }
  }

  public function switchIsActif() {
    if ($this->is_actif) {
      $this->is_actif = 0;
    } else {
      $this->is_actif = 1;
    }
  }
}

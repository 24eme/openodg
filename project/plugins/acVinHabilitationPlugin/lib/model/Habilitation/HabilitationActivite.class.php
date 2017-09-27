<?php
/**
 * Model for HabilitationActivite
 *
 */

class HabilitationActivite extends BaseHabilitationActivite {

  public function updateHabilitation($date,$statut, $commentaire = ""){
      $this->date = $date;
      $this->statut = $statut;
      $this->commentaire = $commentaire;
  }

  public function isHabilite(){
    return ($this->statut == HabilitationClient::STATUT_HABILITE);
  }

  public function isWrongHabilitation(){
      return ($this->statut == HabilitationClient::STATUT_REFUS) ($this->statut == HabilitationClient::STATUT_RETRAIT) ($this->statut == HabilitationClient::STATUT_SUSPENDU);
  }

  public function hasStatut(){
    return $this->statut;
  }

}

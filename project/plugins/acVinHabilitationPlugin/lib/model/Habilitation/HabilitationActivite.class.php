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
}

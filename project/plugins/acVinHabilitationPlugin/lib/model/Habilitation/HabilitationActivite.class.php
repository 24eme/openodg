<?php
/**
 * Model for HabilitationActivite
 *
 */

class HabilitationActivite extends BaseHabilitationActivite {

  public function updateHabilitation($statut, $commentaire = "", $date = ''){
      $this->addHistoriqueActiviteChanges($this->statut,$statut,$commentaire);
      if (!$date) {
        $date = $this->getDocument()->getDate();
      }
      $this->date = $date;
      $this->statut = $statut;
      $this->commentaire = $commentaire;
  }

  public function getProduitHash() {

      return $this->getParent()->getParent()->getHash();
  }

  public function isHabilite(){
    return ($this->statut == HabilitationClient::STATUT_HABILITE) || ($this->statut == HabilitationClient::STATUT_DEMANDE_RETRAIT);
  }

  public function isNonhabilite(){
    return ($this->statut == HabilitationClient::STATUT_DEMANDE_HABILITATION) || ($this->statut == HabilitationClient::STATUT_ANNULE) || !($this->statut);
  }

  public function isWrongHabilitation(){
      return ($this->statut == HabilitationClient::STATUT_REFUS) || ($this->statut == HabilitationClient::STATUT_RETRAIT) || ($this->statut == HabilitationClient::STATUT_SUSPENDU);
  }

  public function hasStatut(){
    return $this->statut;
  }

  private function addHistoriqueActiviteChanges($old_statut,$statut,$commentaire){
    $activite = HabilitationClient::$activites_libelles[$this->getKey()];
    $produitLibelle = $this->getParent()->getParent()->getLibelle();
    if($old_statut == $statut){
      $description = $produitLibelle." : pour l'activité \"".$activite."\", le commentaire a changé";
    }elseif (!$old_statut) {
      $description = $produitLibelle." : activité \"".$activite."\", est passé en statut \"".HabilitationClient::$statuts_libelles[$statut]."\"";
    }else{
      $description = $produitLibelle." : activité \"".$activite."\", statut changé de \"".HabilitationClient::$statuts_libelles[$old_statut]."\" à \"".HabilitationClient::$statuts_libelles[$statut]."\"";
    }
    $this->getDocument()->addHistorique($description, $commentaire);
  }

}

<?php
/**
 * Model for HabilitationActivite
 *
 */

class HabilitationActivite extends BaseHabilitationActivite {

  public function updateHabilitation($date,$statut, $commentaire = ""){
      $date = $this->getDocument()->getDate(); //on verrra si on veux modifier les dates;
      $this->addHistorique($date,$this->statut,$statut,$commentaire);
      $this->date = $date;
      $this->statut = $statut;
      $this->commentaire = $commentaire;
  }

  public function isHabilite(){
    return ($this->statut == HabilitationClient::STATUT_HABILITE);
  }

  public function isWrongHabilitation(){
      return ($this->statut == HabilitationClient::STATUT_REFUS) || ($this->statut == HabilitationClient::STATUT_RETRAIT) || ($this->statut == HabilitationClient::STATUT_SUSPENDU);
  }

  public function hasStatut(){
    return $this->statut;
  }

  public function addHistorique($date,$old_statut,$statut,$commentaire){
    $activite = HabilitationClient::$activites_libelles[$this->getKey()];
    $produitLibelle = $this->getParent()->getParent()->getLibelle();
    $historiqueRow = $this->getDocument()->get('historique')->add(null);
    $historiqueRow->iddoc = $this->getDocument()->_id;
    $historiqueRow->date = $date;
    $historiqueRow->auteur = (sfContext::getInstance()->getUser()->isAdmin())? 'Admin' : sfContext::getInstance()->getUser()->getCompte()->identifiant;
    $historiqueRow->description = ($old_statut)? $produitLibelle." : activité \"".$activite."\", statut changé de \"".HabilitationClient::$statuts_libelles[$old_statut]."\" à \"".HabilitationClient::$statuts_libelles[$statut]."\""  :
                                                 $produitLibelle." : activité \"".$activite."\", est passé en statut \"".HabilitationClient::$statuts_libelles[$statut]."\"";
    $historiqueRow->description .= ($commentaire)? " (".$commentaire.")" : "";
  }

}

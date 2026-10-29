<?php
/**
 * Model for HabilitationActivite
 *
 */

class HabilitationActivite extends BaseHabilitationActivite {

  public function updateHabilitation($statut, $site, $commentaire = "", $date = ''){
      if ($site) {
        $this->add('site', $site);
      }
      if($date == $this->getDocument()->date || !$date) {
        $this->addHistoriqueActiviteChanges($this->statut,$statut,$commentaire);
      }
      if (!$date) {
        $date = $this->getDocument()->getDate();
      }
      $this->date = $date;
      $this->statut = $statut;
      $this->commentaire = $commentaire;
      $this->activite = $this->getActivite();
  }

    public function getLibelle() {

        return HabilitationClient::getInstance()->getActivites()[$this->getKey()];
    }

  public function getProduitHash() {

      return $this->getParent()->getParent()->getHash();
  }

  public function isHabilite(){
    return ($this->statut == HabilitationClient::STATUT_HABILITE) || ($this->statut == HabilitationClient::STATUT_DEMANDE_RETRAIT || $this->statut == HabilitationClient::STATUT_EXTERIEUR);
  }

  public function isNonhabilite(){
    return ($this->statut == HabilitationClient::STATUT_DEMANDE_HABILITATION) || ($this->statut == HabilitationClient::STATUT_ATTENTE_HABILITATION) || ($this->statut == HabilitationClient::STATUT_ANNULE) || !($this->statut);
  }

  public function isWrongHabilitation(){
      return ($this->statut == HabilitationClient::STATUT_REFUS) || ($this->statut == HabilitationClient::STATUT_RETRAIT) || ($this->statut == HabilitationClient::STATUT_SUSPENDU ) || ($this->statut == HabilitationClient::STATUT_RESILIE);
  }

  public function isHabiliteExterieur(){
      return ($this->statut == HabilitationClient::STATUT_EXTERIEUR);
  }

  public function hasStatut(){
    return $this->statut;
  }

  private function addHistoriqueActiviteChanges($old_statut,$statut,$commentaire){
    $activite = HabilitationClient::getInstance()->getLibelleActivite($this->getKey());
    $produitLibelle = $this->getParent()->getParent()->getLibelle();
    $site = '';
    if ($this->hasSite()) {
        $site = " du site ".$this->site;
    }
    if($old_statut == $statut){
      $description = $produitLibelle.$site." : pour l'activité \"".$activite."\", le commentaire a changé";
    }elseif (!$old_statut) {
      $description = $produitLibelle.$site." : activité \"".$activite."\", est passé en statut \"".HabilitationClient::$statuts_libelles[$statut]."\"";
    }else{
      $description = $produitLibelle.$site." : activité \"".$activite."\", statut changé de \"".HabilitationClient::$statuts_libelles[$old_statut]."\" à \"".HabilitationClient::$statuts_libelles[$statut]."\"";
    }
    $this->getDocument()->addHistorique($description, $commentaire, null, $statut);
  }

  public function hasSite() {
    return $this->exist('site') && $this->site;
  }

  public function getActivite() {
      $activite = ($this->exist('activite')) ? $this->_get('activite') : null;

      if($activite && $activite != 'activites') {

          return $activite;
      }
      $activite = preg_replace("/-SITE_[0-9]*$/", "", $this->getKey());
      $this->_set('activite', $activite);
      return $activite;
  }

}

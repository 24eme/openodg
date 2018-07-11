<?php
/**
 * Model for HabilitationCepage
 *
 */

class HabilitationProduit extends BaseHabilitationProduit {

    public function getConfig()
    {
        return $this->getCouchdbDocument()->getConfiguration()->get($this->getHash());
    }

    public function getChildrenNode()
    {
        return $this->details;
    }

    public function getProduitHash() {
        if(strpos($this->getKey(),"_")) {
            $nodes = explode("_",$this->getKey());

            return $this->getCouleur()->getProduitHash()."/".$nodes[0]."/".$nodes[1];
        }

        return $this->getHash();
    }

    public function initActivites()
    {
        $activitesNode = $this->add("activites");
        foreach (HabilitationClient::getInstance()->getActivites() as $activite_key => $libelle) {
          $activitesNode->add($activite_key);
        }
    }

    public function getLibelle() {
      if(!$this->_get('libelle')) {
          $this->libelle = $this->getConfig()->getLibelleComplet();
      }

      return $this->_get('libelle');
    }

    public function getLibelleComplet()
    {

        return $this->getLibelle();
    }

    public function isActive()
    {

        return true;
    }

    public function getProduitHashStr(){

        return KeyInflector::slugify($this->getHash());
    }
    public function getNbActivites() {
      return count($this->activites);
    }

    public function getNbActivitesSaisies(){
      $cpt = 0;
      foreach ($this->activites as $key => $activite) {
        if($activite->hasStatut()){
          $cpt++;
        }
      }
      return $cpt;
    }


    public function hasHabilitations(){
      foreach ($this->activites as $key => $activite) {
        if($activite->statut) return true;
      }
      return false;
    }

    public function updateHabilitation($activite, $statut, $commentaire = "", $date = ''){
        if (!$this->exist('activites')) {
          $this->initActivites();
        }
        if (!$this->activites->exist($activite)) {
          throw new sfException('activite '.$activite.' non supportée');
        }
        return $this->activites[$activite]->updateHabilitation($statut, $commentaire, $date);
    }


}

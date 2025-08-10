<?php
/**
 * Model for HabilitationCepage
 *
 */

class HabilitationProduit extends BaseHabilitationProduit {

    public function getConfig($date = null)
    {
        return $this->getCouchdbDocument()->getConfiguration($date)->get($this->getHash());
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
          $this->libelle = preg_replace('/ Tranquilles?$/', '', $this->getConfig()->getLibelleComplet();
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

    public function getActivitesHabilites() {
        $activites = array();
        foreach ($this->activites as $key => $activite) {
            if(!$activite->isHabilite()){
                continue;
            }

            $activites[$key] = $activite;
        }

        return $activites;
    }

    public function getActivitesWrongHabilitation() {
        $activites = array();
        foreach ($this->activites as $key => $activite) {
            if(!$activite->isWrongHabilitation()){
                continue;
            }

            $activites[$key] = $activite;
        }

        return $activites;
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

    public function updateHabilitation($activite, $sites, $statut, $commentaire = "", $date = ''){
        if (!$this->exist('activites')) {
          $this->initActivites();
        }
        if (!$this->activites->exist($activite)) {
            $this->initActivites();
            if (!$this->activites->exist($activite)) {
                throw new sfException('activite '.$activite.' non supportée');
            }
        }
        if (!$sites || !count($sites)) {
            $a = $this->add('activites')->add($activite);
            $this->activites->get($activite)->updateHabilitation($statut, null, $commentaire, $date);
        }else {
            foreach($sites as $k => $site) {
                $kactivite = $activite.'-'.$k;
                $a = $this->add('activites')->add($kactivite);
                $this->activites->get($kactivite)->updateHabilitation($statut, $site, $commentaire, $date);
            }
        }
    }

    public function isHabiliteFor($activite) {
        if(!$this->exist('activites')) {
            return false;
        }
        foreach($this->activites as $a) {
            if ($activite === null && $a->isHabilite()) {
                return true;
            }

            if(strpos($a->activite, $activite) !==  0) {
                continue;
            }

            if($a->isHabilite()) {
                return true;
            }
        }

        return false;
    }


}

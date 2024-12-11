<?php

class CompteRoute extends sfObjectRoute implements InterfaceCompteRoute {

    protected $compte = null;
    protected $accesses = array();

    protected function getObjectForParameters($parameters = null) {
      $this->compte = CompteClient::getInstance()->find(CompteClient::getInstance()->getId($parameters['identifiant']));

      $myUser = sfContext::getInstance()->getUser();
      $allowed = $myUser->isAdmin();
      $allowed = $allowed || (isset($this->accesses['allow_stalker']) && $this->accesses['allow_stalker'] && $myUser->isStalker());
      $allowed = $allowed || (isset($this->accesses['allow_habilitation']) && $this->accesses['allow_habilitation'] && $myUser->hasHabilitation() && $this->compte->getSociete()->type_societe != SocieteClient::TYPE_AUTRE);
      $allowed = $allowed || (isset($this->accesses['allow_admin_odg']) && $this->accesses['allow_admin_odg'] && $myUser->isAdminODG());
      if ($allowed) {
          return $this->compte;
      }
      if ($myUser->isAdminODG() && $this->getSociete()) {
          $compteUser = $myUser->getCompte();
          $region = Organisme::getInstance()->getCurrentRegion();
          if ($region) {
              foreach($this->getSociete()->getEtablissementsObj() as $e) {
                  if (HabilitationClient::getInstance()->isRegionInHabilitation($e->etablissement->identifiant, $region)) {
                      return $this->compte;
                  }
              }
          }
          throw new sfError403RegionException($compteUser);
      }
      if ($myUser->hasDrevAdmin()) {
          return $this->compte;
      }
      if ($myUser->hasTeledeclaration()
            && $myUser->getCompte()->identifiant == $this->getCompte()->getSociete()->getMasterCompte()->identifiant
            && $this->getCompte()->getSociete()->type_societe == SocieteClient::TYPE_OPERATEUR)
      {
          return $this->compte;
      }
      throw new sfError403Exception("Vous n'avez pas le droit d'accéder à cette page (Compte)");
    }

    protected function doConvertObjectToArray($object) {
      return array("identifiant" => $object->getIdentifiant());
    }

    public function getSociete() {
        if (!isset($this->societe) || !$this->societe) {
           $this->societe = $this->getCompte()->getSociete();
      }
      return $this->societe;
    }

    public function getCompte($parameters = null) {
      if (is_array($parameters)) foreach($parameters as $k => $v) {
          if (strpos($k, 'allow') === false) {
              continue;
          }
          $this->accesses[$k] = $v;
      }
      if (!$this->compte) {
           $this->compte = $this->getObject();
      }
      return $this->compte;
    }
}

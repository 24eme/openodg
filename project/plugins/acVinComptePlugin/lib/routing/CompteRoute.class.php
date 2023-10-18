<?php

class CompteRoute extends sfObjectRoute implements InterfaceCompteRoute {

    protected $compte = null;
    protected $accesses = array();

    protected function getObjectForParameters($parameters = null) {
      $this->compte = CompteClient::getInstance()->find(CompteClient::getInstance()->getId($parameters['identifiant']));

      $myUser = sfContext::getInstance()->getUser();
      if ($myUser->isAdmin() || (isset($this->accesses['allow_admin_odg']) && $this->accesses['allow_admin_odg'] && $myUser->isAdminODG())) {
          return $this->compte;
      }
      if ($myUser->isAdminODG() && $this->getSociete()) {
          $compteUser = $myUser->getCompte();
          $region = $compteUser->region;
          if ($region) {
              foreach($this->getSociete()->getEtablissementsObj() as $e) {
                  if (HabilitationClient::getInstance()->isRegionInHabilitation($e->identifiant, $region)) {
                      return $this->compte;
                  }
              }
          }
          throw new sfError403Exception("Vous n'avez pas le droit d'accéder à cette page (region)");
      }
      if ($myUser->hasTeledeclaration() && !$myUser->hasDrevAdmin()
            && $myUser->getCompte()->identifiant != $this->getCompte()->getSociete()->getMasterCompte()->identifiant)
      {
            throw new sfError403Exception("Vous n'avez pas le droit d'accéder à cette page");
      }
      if($myUser->hasCredential(myUser::CREDENTIAL_HABILITATION)
            && $myUser->getCompte()->identifiant != $this->getCompte()->getSociete()->getMasterCompte()->identifiant
            && $this->getCompte()->getSociete()->type_societe != SocieteClient::TYPE_OPERATEUR)
      {
          throw new sfError403Exception("Vous n'avez pas le droit d'accéder à cette page");
      }
      return $this->compte;
    }

    protected function doConvertObjectToArray($object) {
      return array("identifiant" => $object->getIdentifiant());
    }

    public function getSociete() {
      if (!$this->societe) {
           $this->societe = $this->getCompte()->getSociete();
      }
      return $this->societe;
    }

    public function getCompte($parameters = null) {
      if (isset($parameters['allow_admin_odg'])){
          $this->accesses['allow_admin_odg'] = $parameters['allow_admin_odg'];
      }
      if (!$this->compte) {
           $this->compte = $this->getObject();
      }
      return $this->compte;
    }
}

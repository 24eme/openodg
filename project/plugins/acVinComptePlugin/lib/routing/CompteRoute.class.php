<?php

class CompteRoute extends sfObjectRoute implements InterfaceCompteRoute {

    protected $compte = null;

    protected function getObjectForParameters($parameters = null) {
      $this->compte = CompteClient::getInstance()->find(CompteClient::getInstance()->getId($parameters['id']));
      return $this->compte;
    }

    protected function doConvertObjectToArray($object = null) {
      $this->compte = $object;
      return array("id" => $object->getIdentifiant());
    }

    public function getCompte() {
      if (!$this->compte) {
           $this->compte = $this->getObject();
      }
      return $this->compte;
    }
}

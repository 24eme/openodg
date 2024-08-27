<?php
class CompteRoute extends sfObjectRoute {

    protected $compte = null;

    protected function getObjectForParameters($parameters) {
        if(!isset($parameters['id']) && isset($parameters['identifiant'])) {
            $parameters['id'] = $parameters['identifiant'];
        }
        $this->compte = CompteClient::getInstance()->find("COMPTE-".str_replace("COMPTE-", "", $parameters['id']));
        if (!$this->compte) {

            throw new sfError404Exception(sprintf('No compte found with the id "%s".', $parameters['id']));
        }
        return $this->compte;
    }

    protected function doConvertObjectToArray($object) {  
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getCompte() {
        if (!$this->compte) {
            $this->getObject();
        }
        return $this->compte;
    }

}
<?php
class CompteRoute extends sfObjectRoute {

    protected $compte = null;

    protected function getObjectForParameters($parameters) {

        $this->compte = CompteClient::getInstance()->findByIdentifiant($parameters['id']);
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
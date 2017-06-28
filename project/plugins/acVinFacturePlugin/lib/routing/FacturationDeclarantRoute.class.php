<?php
class FacturationDeclarantRoute extends CompteRoute implements InterfaceFacturationRoute {

    protected $compte = null;

    protected function getObjectForParameters($parameters) {

        $this->compte = CompteClient::getInstance()->find($parameters['id']);
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

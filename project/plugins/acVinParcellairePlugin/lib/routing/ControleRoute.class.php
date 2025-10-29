<?php
class ControleRoute extends EtablissementRoute implements InterfaceDeclarationRoute {

    protected $controle = null;

    protected function getObjectForParameters($parameters = null) {
        $this->controle = ControleClient::getInstance()->find($parameters['id']);
        if (!$this->controle) {
            throw new sfError404Exception(sprintf('No controle found with the id "%s".', $parameters['id']));
        }
        return $this->controle;
    }

    protected function doConvertObjectToArray($object = null) {
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getControle() {
        if (!$this->controle) {
            $this->getObject();
        }
        return $this->controle;
    }

    public function getEtablissement($parameters = null) {

        return $this->getControle()->getEtablissementObject();
    }
}

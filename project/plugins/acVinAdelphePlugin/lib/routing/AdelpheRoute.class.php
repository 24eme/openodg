<?php
class AdelpheRoute extends EtablissementRoute implements InterfaceDeclarationRoute {

    protected $adelphe = null;

    protected function getObjectForParameters($parameters = null) {
        $this->adelphe = AdelpheClient::getInstance()->find($parameters['id']);
        if (!$this->adelphe) {
            throw new sfError404Exception(sprintf('No Adelphe found with the id "%s".', $parameters['id']));
        }
        parent::getObjectForParameters(array('identifiant' => $this->adelphe->identifiant));
        return $this->adelphe;
    }

    protected function doConvertObjectToArray($object = null) {
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getAdelphe() {
        if (!$this->adelphe) {
            $this->getObject();
        }
        return $this->adelphe;
    }

}

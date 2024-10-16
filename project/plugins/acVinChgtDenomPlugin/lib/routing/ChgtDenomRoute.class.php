<?php
class ChgtDenomRoute extends EtablissementRoute implements InterfaceDeclarationRoute {

    protected $obj = null;

    protected function getObjectForParameters($parameters = null) {
        $this->obj = ChgtDenomClient::getInstance()->find($parameters['id']);
        if (!$this->obj) {

            throw new sfError404Exception(sprintf('No ChgtDenom found with the id "%s".', $parameters['id']));
        }
        parent::getObjectForParameters(array('identifiant' => $this->obj->identifiant));
        return $this->obj;
    }

    protected function doConvertObjectToArray($object = null) {
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getChgtDenom($parameters = null) {
        $this->getEtablissement($parameters);
        if (!$this->obj) {
            $this->getObject();
        }
        return $this->obj;
    }

}

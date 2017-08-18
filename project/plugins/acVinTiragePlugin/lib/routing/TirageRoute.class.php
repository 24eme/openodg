<?php

class TirageRoute extends EtablissementRoute implements InterfaceDeclarationRoute {

    protected $tirage = null;

    protected function getObjectForParameters($parameters = null) {

        $this->tirage = TirageClient::getInstance()->find($parameters['id']);
        if (!$this->tirage) {

            throw new sfError404Exception(sprintf('No declaration Tirage found with the id "%s".', $parameters['id']));
        }
        return $this->tirage;
    }

    protected function doConvertObjectToArray($object = null) {
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getTirage() {
        if (!$this->tirage) {
            $this->getObject();
        }
        return $this->tirage;
    }

    public function getEtablissement() {

        return $this->getTirage()->getEtablissementObject();
    }

}

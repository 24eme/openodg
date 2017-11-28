<?php

class TravauxMarcRoute extends EtablissementRoute implements InterfaceDeclarationRoute {

    protected $travauxMarc = null;

    protected function getObjectForParameters($parameters) {

        $this->travauxMarc = TravauxMarcClient::getInstance()->find($parameters['id']);
        if (!$this->travauxMarc) {

            throw new sfError404Exception(sprintf('No TravauxMarc found with the id "%s".', $parameters['id']));
        }
        return $this->travauxMarc;
    }

    protected function doConvertObjectToArray($object) {
        $parameters = array("id" => $object->_id);

        return $parameters;
    }

    public function getTravauxMarc() {
        if (!$this->travauxMarc) {
            $this->getObject();
        }

        return $this->travauxMarc;
    }

    public function getEtablissement() {

        return $this->getTravauxMarc()->getEtablissementObject();
    }
}

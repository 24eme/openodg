<?php
class DRevRoute extends EtablissementRoute implements InterfaceDeclarationRoute {

    protected $drev = null;

    protected function getObjectForParameters($parameters = null) {

        $this->drev = DRevClient::getInstance()->find($parameters['id']);
        if (!$this->drev) {

            throw new sfError404Exception(sprintf('No DRev found with the id "%s".', $parameters['id']));
        }
        return $this->drev;
    }

    protected function doConvertObjectToArray($object) {
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getDRev() {
        if (!$this->drev) {
            $this->getObject();
        }
        return $this->drev;
    }

    public function getEtablissement($parameters = null) {

        return $this->getDRev()->getEtablissementObject();
    }

}

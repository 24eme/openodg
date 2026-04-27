<?php
class DRaPRoute extends EtablissementRoute implements InterfaceDeclarationRoute {

    protected $drap = null;

    protected function getObjectForParameters($parameters = null) {
        $this->drap = DRaPClient::getInstance()->find($parameters['id']);
        if (!$this->drap) {

            throw new sfError404Exception(sprintf('No DRaP found with the id "%s".', $parameters['id']));
        }
        parent::getObjectForParameters(array('identifiant' => $this->drap->identifiant));
        return $this->drap;
    }

    protected function doConvertObjectToArray($object = null) {
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getDRaP() {
        if (!$this->drap) {
            $this->getObject();
        }
        return $this->drap;
    }
}

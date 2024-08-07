<?php

class DegustationRoute extends sfObjectRoute implements InterfaceDegustationGeneralRoute
{
    protected $degustation = null;

    protected function getObjectForParameters($parameters) {

        $this->degustation = DegustationClient::getInstance()->find($parameters['id']);

        if (!$this->degustation) {

            throw new sfError404Exception(sprintf("Pas de degustation trouvé avec l'id \"%s\"", $parameters['id']));
        }
        return $this->degustation;
    }

    protected function doConvertObjectToArray($object) {
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getDegustation() {
        if (!$this->degustation) {
            $this->getObject();
        }
        DegustationEtapes::getInstance($this->degustation);
        return $this->degustation;
    }
}

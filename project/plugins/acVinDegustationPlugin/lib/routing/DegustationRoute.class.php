<?php
class DegustationRoute extends sfObjectRoute {

    protected $degustation = null;

    protected function getObjectForParameters($parameters) {

        $this->degustation = DegustationClient::getInstance()->find($parameters['id']);
        if (!$this->degustation) {

            throw new sfError404Exception(sprintf('No Degustation found with the id "%s".', $parameters['id']));
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
        return $this->degustation;
    }

}
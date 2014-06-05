<?php
class DRevRoute extends sfObjectRoute {

    protected $drev = null;

    protected function getObjectForParameters($parameters) {

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

}
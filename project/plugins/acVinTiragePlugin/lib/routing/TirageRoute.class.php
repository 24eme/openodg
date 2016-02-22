<?php

class TirageRoute extends sfObjectRoute {

    protected $tirage = null;

    protected function getObjectForParameters($parameters) {

        $this->tirage = TirageClient::getInstance()->find($parameters['id']);
        if (!$this->tirage) {

            throw new sfError404Exception(sprintf('No declaration Tirage found with the id "%s".', $parameters['id']));
        }
        return $this->tirage;
    }

    protected function doConvertObjectToArray($object) {  
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getTirage() {
        if (!$this->tirage) {
            $this->getObject();
        }
        return $this->tirage;
    }

}
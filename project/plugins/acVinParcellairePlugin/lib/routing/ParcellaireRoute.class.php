<?php
class ParcellaireRoute extends sfObjectRoute {

    protected $parcellaire = null;

    protected function getObjectForParameters($parameters) {

        $this->parcellaire = ParcellaireClient::getInstance()->find($parameters['id']);
        if (!$this->parcellaire) {

            throw new sfError404Exception(sprintf('No Parcellaire found with the id "%s".', $parameters['id']));
        }
        return $this->parcellaire;
    }

    protected function doConvertObjectToArray($object) {  
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getParcellaire() {
        if (!$this->parcellaire) {
            $this->getObject();
        }
        return $this->parcellaire;
    }

}
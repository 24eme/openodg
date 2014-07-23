<?php
class EtablissementRoute extends sfObjectRoute {

    protected $etablissement = null;

    protected function getObjectForParameters($parameters) {

        $this->etablissement = EtablissementClient::getInstance()->find($parameters['id']);
        if (!$this->etablissement) {

            throw new sfError404Exception(sprintf('No etablissement found with the id "%s".', $parameters['id']));
        }
        return $this->etablissement;
    }

    protected function doConvertObjectToArray($object) {  
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getEtablissement() {
        if (!$this->etablissement) {
            $this->getObject();
        }
        return $this->etablissement;
    }

}
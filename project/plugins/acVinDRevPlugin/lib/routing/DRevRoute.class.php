<?php
class DRevRoute extends EtablissementRoute implements InterfaceDeclarationRoute {

    protected $drev = null;

    protected function getObjectForParameters($parameters = null) {
        if ($this->drev) {
            return $this->drev;
        }
        $this->drev = DRevClient::getInstance()->find($parameters['id']);
        if (!$this->drev) {

            throw new sfError404Exception(sprintf('No DRev found with the id "%s".', $parameters['id']));
        }
        parent::getObjectForParameters(array('identifiant' => $this->drev->identifiant));
        return $this->drev;
    }

    protected function doConvertObjectToArray($object = null) {
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getDRev($parameters = null) {
        $this->getEtablissement($parameters);
        if (!$this->drev) {
            $this->getObject();
        }
        return $this->drev;
    }

}

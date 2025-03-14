<?php
class ConditionnementRoute extends EtablissementRoute implements InterfaceDeclarationRoute {

    protected $doc = null;

    protected function getObjectForParameters($parameters = null) {
        $this->doc = ConditionnementClient::getInstance()->find($parameters['id']);
        if (!$this->doc) {

            throw new sfError404Exception(sprintf('No Conditionnement found with the id "%s".', $parameters['id']));
        }
        parent::getObjectForParameters(array_merge($parameters, array('identifiant' => $this->doc->identifiant)));
        return $this->doc;
    }

    protected function doConvertObjectToArray($object = null) {
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getConditionnement($parameters = null) {
        if ($parameters) {
            $this->parameters = array_merge($this->parameters, $parameters);
        }
        if (!$this->doc) {
            $this->getObject();
        }
        return $this->doc;
    }

}

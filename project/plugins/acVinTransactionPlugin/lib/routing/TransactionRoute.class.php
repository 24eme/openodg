<?php
class TransactionRoute extends EtablissementRoute implements InterfaceDeclarationRoute {

    protected $doc = null;

    protected function getObjectForParameters($parameters = null) {
        $this->doc = TransactionClient::getInstance()->find($parameters['id']);
        if (!$this->doc) {

            throw new sfError404Exception(sprintf('No Transaction found with the id "%s".', $parameters['id']));
        }
        parent::getObjectForParameters(array('identifiant' => $this->doc->identifiant));
        return $this->doc;
    }

    protected function doConvertObjectToArray($object = null) {
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getTransaction() {
        if (!$this->doc) {
            $this->getObject();
        }
        return $this->doc;
    }

}

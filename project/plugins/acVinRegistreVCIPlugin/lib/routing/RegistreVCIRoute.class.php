<?php
class RegistreVCIRoute extends EtablissementRoute implements InterfaceDeclarationRoute {

    protected $drev = null;

    protected function getObjectForParameters($parameters) {

        $this->registre = RegistreVCIClient::getInstance()->find($parameters['id']);
        if (!$this->registre) {

            throw new sfError404Exception(sprintf('No DRev found with the id "%s".', $parameters['id']));
        }
        return $this->registre;
    }

    protected function doConvertObjectToArray($object) {
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getRegistreVCI() {
        if (!isset($this->registre) || !$this->registre) {
            $this->getObject();
        }
        return $this->registre;
    }

    public function getEtablissement() {

        return $this->getRegistreVCI()->getEtablissementObject();
    }

}

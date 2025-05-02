<?php
class ParcellaireRoute extends EtablissementRoute implements InterfaceParcellaireRoute {

    protected $parcellaire = null;

    protected function getObjectForParameters($parameters = null) {
        $this->parcellaire = ParcellaireClient::getInstance()->find($parameters['id']);
        if (!$this->parcellaire) {

            throw new sfError404Exception(sprintf('No Parcellaire found with the id "%s".', $parameters['id']));
        }
        return $this->parcellaire;
    }

    protected function doConvertObjectToArray($object = null) {
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getParcellaire() {
        if (!$this->parcellaire) {
            $this->getObject();
        }
        return $this->parcellaire;
    }

    public function getEtablissement($parameters = null) {

        return $this->getParcellaire()->getEtablissementObject();
    }
}

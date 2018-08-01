<?php
class ParcellaireIrrigueRoute extends EtablissementRoute implements InterfaceDeclarationRoute {

    protected $parcellaireIrrigue = null;

    protected function getObjectForParameters($parameters = null) {
        $this->parcellaireIrrigue = ParcellaireIrrigueClient::getInstance()->find($parameters['id']);
        if (!$this->parcellaireIrrigue) {

            throw new sfError404Exception(sprintf('No ParcellaireIrrigue found with the id "%s".', $parameters['id']));
        }
        return $this->parcellaireIrrigue;
    }

    protected function doConvertObjectToArray($object = null) {
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getParcellaireIrrigue() {
        if (!$this->parcellaireIrrigue) {
            $this->getObject();
        }
        return $this->parcellaireIrrigue;
    }

    public function getEtablissement() {

        return $this->getParcellaireIrrigable()->getEtablissementObject();
    }
}

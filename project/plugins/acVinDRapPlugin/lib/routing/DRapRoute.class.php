<?php
class ParcellaireIrrigableRoute extends EtablissementRoute implements InterfaceDeclarationRoute {

    protected $parcellaireIrrigable = null;

    protected function getObjectForParameters($parameters = null) {
        $this->parcellaireIrrigable = ParcellaireIrrigableClient::getInstance()->find($parameters['id']);
        if (!$this->parcellaireIrrigable) {

            throw new sfError404Exception(sprintf('No ParcellaireIrrigable found with the id "%s".', $parameters['id']));
        }
        parent::getObjectForParameters(array('identifiant' => $this->parcellaireIrrigable->identifiant));
        return $this->parcellaireIrrigable;
    }

    protected function doConvertObjectToArray($object = null) {
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getParcellaireIrrigable() {
        if (!$this->parcellaireIrrigable) {
            $this->getObject();
        }
        return $this->parcellaireIrrigable;
    }
}

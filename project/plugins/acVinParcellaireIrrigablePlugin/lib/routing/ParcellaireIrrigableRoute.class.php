<?php
class ParcellaireIrrigableRoute extends EtablissementRoute implements InterfaceDeclarationRoute {

    protected $parcellaireIrrigableIrrigable = null;

    protected function getObjectForParameters($parameters = null) {
        $this->parcellaireIrrigable = ParcellaireIrrigable::getInstance()->find($parameters['id']);
        if (!$this->parcellaireIrrigable) {

            throw new sfError404Exception(sprintf('No ParcellaireIrrigable found with the id "%s".', $parameters['id']));
        }
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

    public function getEtablissement() {

        return $this->getParcellaireIrrigable()->getEtablissementObject();
    }
}

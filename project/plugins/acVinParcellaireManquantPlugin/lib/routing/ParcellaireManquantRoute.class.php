<?php
class ParcellaireManquantRoute extends EtablissementRoute implements InterfaceDeclarationRoute {

    protected $parcellaireManquant = null;

    protected function getObjectForParameters($parameters = null) {
        $this->parcellaireManquant = ParcellaireManquantClient::getInstance()->find($parameters['id']);
        if (!$this->parcellaireManquant) {

            throw new sfError404Exception(sprintf('No ParcellaireManquant found with the id "%s".', $parameters['id']));
        }
        parent::getObjectForParameters(array('identifiant' => $this->parcellaireManquant->identifiant));
        return $this->parcellaireManquant;
    }

    protected function doConvertObjectToArray($object = null) {
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getParcellaireManquant() {
        if (!$this->parcellaireManquant) {
            $this->getObject();
        }
        return $this->parcellaireManquant;
    }
}

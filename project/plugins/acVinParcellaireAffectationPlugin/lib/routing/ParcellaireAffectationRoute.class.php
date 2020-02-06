<?php
class ParcellaireAffectationRoute extends EtablissementRoute implements InterfaceDeclarationRoute {

    protected $ParcellaireAffectation = null;

    protected function getObjectForParameters($parameters = null) {
        $this->ParcellaireAffectation = ParcellaireAffectationClient::getInstance()->find($parameters['id']);
        if (!$this->ParcellaireAffectation) {

            throw new sfError404Exception(sprintf('No ParcellaireAffectation found with the id "%s".', $parameters['id']));
        }
        return $this->ParcellaireAffectation;
    }

    protected function doConvertObjectToArray($object = null) {
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getParcellaireAffectation() {
        if (!$this->ParcellaireAffectation) {
            $this->getObject();
        }
        return $this->ParcellaireAffectation;
    }

    public function getEtablissement() {

        return $this->getParcellaireIrrigable()->getEtablissementObject();
    }
}

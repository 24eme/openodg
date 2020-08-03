<?php
class parcellaireAffectationRoute extends EtablissementRoute implements InterfaceDeclarationRoute {

    protected $parcellaireAffectation = null;

    protected function getObjectForParameters($parameters = null) {
        $this->parcellaireAffectation = parcellaireAffectationClient::getInstance()->find($parameters['id']);
        if (!$this->parcellaireAffectation) {

            throw new sfError404Exception(sprintf('No parcellaireAffectation found with the id "%s".', $parameters['id']));
        }
        return $this->parcellaireAffectation;
    }

    protected function doConvertObjectToArray($object = null) {
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getParcellaireAffectation() {
        if (!$this->parcellaireAffectation) {
            $this->getObject();
        }
        return $this->parcellaireAffectation;
    }

    public function getEtablissement() {

        return $this->getParcellaireAffectation()->getEtablissementObject();
    }
}

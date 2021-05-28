<?php
class ParcellaireAffectationCoopRoute extends EtablissementRoute implements InterfaceDeclarationRoute {

    protected $parcellaireAffectationCoop = null;

    protected function getObjectForParameters($parameters = null) {
        $this->parcellaireAffectationCoop = parcellaireAffectationCoopClient::getInstance()->find($parameters['id']);
        if (!$this->parcellaireAffectationCoop) {

            throw new sfError404Exception(sprintf('No parcellaireAffectationCoop found with the id "%s".', $parameters['id']));
        }
        parent::getObjectForParameters(array('identifiant' => $this->parcellaireAffectationCoop->identifiant));
        return $this->parcellaireAffectationCoop;
    }

    protected function doConvertObjectToArray($object = null) {
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getParcellaireAffectationCoop() {
        if (!$this->parcellaireAffectationCoop) {
            $this->getObject();
        }
        return $this->parcellaireAffectationCoop;
    }
}

<?php
class ParcellaireAffectationRoute extends EtablissementRoute implements InterfaceDeclarationRoute {

    protected $parcellaire = null;

    protected function getObjectForParameters($parameters = null) {
        $this->parcellaire = ParcellaireAffectationClient::getInstance()->find($parameters['id']);
        if (!$this->parcellaire) {

            throw new sfError404Exception(sprintf('No Parcellaire found with the id "%s".', $parameters['id']));
        }
        parent::getObjectForParameters(array('identifiant' => $this->parcellaire->identifiant));
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

}

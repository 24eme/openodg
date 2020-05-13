<?php

class ParcellaireIntentionAffectationRoute extends sfRoute implements InterfaceDeclarationRoute
{
	protected $parcellaireIntentionAffectation = null;

    protected function getObjectForParameters($parameters = null) {
        $this->parcellaireIntentionAffectation = parcellaireAffectationClient::getInstance()->find($parameters['id']);
        if (!$this->parcellaireIntentionAffectation) {

            throw new sfError404Exception(sprintf('No parcellaireIntentionAffectation found with the id "%s".', $parameters['id']));
        }
        return $this->parcellaireIntentionAffectation;
    }

    protected function doConvertObjectToArray($object = null) {
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getParcellaireIntentionAffectation() {
        if (!$this->parcellaireIntentionAffectation) {
            $this->getObject();
        }
        return $this->parcellaireIntentionAffectation;
    }

    public function getEtablissement() {

        return $this->getParcellaireIntentionAffectation()->getEtablissementObject();
    }
}

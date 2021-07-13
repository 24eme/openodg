<?php
class FactureRoute extends CompteRoute implements InterfaceFacturationRoute {

    protected $facture = null;

    protected function getObjectForParameters($parameters = null) {

        $this->facture = FactureClient::getInstance()->find($parameters['id']);
        if (sfContext::getInstance()->getUser()->hasTeledeclaration() && sfContext::getInstance()->getUser()->getCompte()->id_societe != $this->facture->getSociete()->_id) {
            throw new sfError404Exception("Vous n'avez pas le droit d'accéder à cette page");
        }
        if (!$this->facture) {

            throw new sfError404Exception(sprintf('No Facture found with the id "%s".', $parameters['id']));
        }
        return $this->facture;
    }

    protected function doConvertObjectToArray($object = null) {
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getFacture() {
        if (!$this->facture) {
            $this->getObject();
        }
        return $this->facture;
    }

    public function getCompte() {

        return $this->getFacture()->getCompte();
    }

}

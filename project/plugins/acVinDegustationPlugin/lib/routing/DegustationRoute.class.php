<?php

class DegustationRoute extends sfObjectRoute implements InterfaceDegustationGeneralRoute
{
    protected $degustation = null;

    /** @var $parameters['id'] De forme DEGUSTATION-20XXXXXXXXXX, TOURNEE-20XXXXXXXXXX, 20XXXXXXXXXX */
    protected function getObjectForParameters($parameters) {
        $id = $parameters['id'];

        // Si pas de tiret dans l'id, alors cela vient d'un lien raccourci d'un mail de dégustation
        // On rajoute alors DEGUSTATION-
        // Sinon, c'est un lien normal soit d'une Dégustation, soit d'une Tournée
        if (strpos($id, '-') === false) {
            $id = "DEGUSTATION-$id";
        }
        $this->degustation = DegustationClient::getInstance()->find($id);

        if (!$this->degustation) {

            throw new sfError404Exception(sprintf("Pas de degustation trouvé avec l'id \"%s\"", $parameters['id']));
        }
        return $this->degustation;
    }

    protected function doConvertObjectToArray($object) {
        $parameters = array("id" => $object->_id);
        return $parameters;
    }

    public function getDegustation() {
        if (!$this->degustation) {
            $this->getObject();
        }
        DegustationEtapes::getInstance($this->degustation);
        return $this->degustation;
    }
}

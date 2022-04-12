<?php
class EtablissementRoute extends sfObjectRoute {

    protected $etablissement = null;

    protected function getObjectForParameters($parameters) {
        $this->etablissement = EtablissementClient::getInstance()->find("ETABLISSEMENT-".$parameters['identifiant']);
        
        if (!EtablissementSecurity::getInstance(sfContext::getInstance()->getUser(), $this->etablissement)->isAuthorized(array())) {

            throw new sfError403Exception("Vous n'avez pas le droit d'accéder à cette page");
        }

        if (!$this->etablissement) {

            throw new sfError404Exception(sprintf('No etablissement found with the id "%s".', $parameters['identifiant']));
        }
        return $this->etablissement;
    }

    protected function doConvertObjectToArray($object) {
        $parameters = array("identifiant" => $object->identifiant);
        return $parameters;
    }

    public function getEtablissement() {
        if (!$this->etablissement) {
            $this->getObject();
        }
        return $this->etablissement;
    }

}

<?php

class EtablissementRoute extends sfObjectRoute implements InterfaceEtablissementRoute {

    protected $etablissement = null;

    protected function getObjectForParameters($parameters = null) {
        $this->etablissement = EtablissementClient::getInstance()->find($parameters['identifiant']);
        $myUser = sfContext::getInstance()->getUser();
        if ($myUser->hasTeledeclaration() && !$myUser->hasDrevAdmin() &&
                $myUser->getCompte()->identifiant != $this->getEtablissement()->getSociete()->getMasterCompte()->identifiant) {

            throw new sfError403Exception("Vous n'avez pas le droit d'accéder à cette page");
        }

        if($myUser->hasDrevAdmin() && !$myUser->isAdmin()) {
            $ids = DRevClient::getInstance()->getHistory($this->getEtablissement()->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND)->getIds();
            $region = $this->getEtablissement()->getSociete()->getMasterCompte()->region;
            $drev = null;
            if(count($ids)) {
                $drev = DRevClient::getInstance()->find($ids[0]);
            }
            if(!$region || !$drev || !count($drev->getProduitsWithoutLots($region))) {
                throw new sfError403Exception("Vous n'avez pas le droit d'accéder à cette page");
            }
        }

        $module = sfContext::getInstance()->getRequest()->getParameterHolder()->get('module');
        sfContext::getInstance()->getResponse()->setTitle(strtoupper($module).' - '.$this->etablissement->nom);
        return $this->etablissement;
    }

    protected function doConvertObjectToArray($object = null) {

        return array("identifiant" => $object->getIdentifiant());
    }

    public function getEtablissement() {

	if (!$this->etablissement) {
           $this->etablissement = $this->getObject();
      	}

	return $this->etablissement;
    }
}

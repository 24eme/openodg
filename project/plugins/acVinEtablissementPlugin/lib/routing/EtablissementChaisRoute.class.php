<?php

class EtablissementChaisRoute extends sfObjectRoute implements InterfaceEtablissementRoute {

    protected $etablissement = null;
    protected $chaisid = null;

    protected function getObjectForParameters($parameters = null) {
        $identifiant = $parameters['identifiant'];
        $splited = explode('C', $identifiant);
        $etablissement_id = $splited[0];
        $this->etablissement = EtablissementClient::getInstance()->find($etablissement_id);
        if (!$this->etablissement) {
            throw new sfException('Etablissement '.$etablissement_id.' non trouvé');
        }
        if (isset($splited[1])) {
            $this->chaisid = intval($splited[1]);
        }
        $myUser = sfContext::getInstance()->getUser();
        $compteUser = $myUser->getCompte();
        if ($myUser->hasTeledeclaration() && !$myUser->hasDrevAdmin() &&
                $compteUser->identifiant != $this->getEtablissement()->getSociete()->getMasterCompte()->identifiant) {

            throw new sfError403Exception("Vous n'avez pas le droit d'accéder à cette page");
        }

        if($myUser->hasDrevAdmin() && !$myUser->isAdmin()) {
            $region = $compteUser->region;
            if(!$region || (!DrevConfiguration::getInstance()->hasHabilitationINAO() && !HabilitationClient::getInstance()->isRegionInHabilitation($this->etablissement->identifiant, $region))) {
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
            $this->getObject();
      	}

	    return $this->etablissement;
    }
}

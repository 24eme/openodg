<?php

class EtablissementChaisRoute extends sfObjectRoute implements InterfaceEtablissementRoute {

    protected $etablissement = null;
    protected $chaisid = null;

    protected function getObjectForParameters($parameters = null) {
        $identifiant = $parameters['identifiant'];
        $etablissement_id = EtablissementChais::getIdentifiantEtablissementPart($identifiant);
        $this->etablissement = EtablissementClient::getInstance()->find($etablissement_id);
        if (!$this->etablissement) {
            throw new sfException('Etablissement '.$etablissement_id.' non trouvé');
        }
        $chai_id = EtablissementChais::getIdentifiantChaiPart($identifiant);
        if ($chai_id) {
            $this->chaisid = $chai_id;
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
        if (!$object->getHash()) {
            return array("identifiant" => $object->getIdentifiant());
        }
        return array("identifiant" => sprintf('%sC%02d', $object->getDocument()->identifiant, $object->getKey()) );
    }

    public function getEtablissement() {

	    if (!$this->etablissement) {
            $this->getObject();
      	}

	    return $this->etablissement;
    }
    
    public function getIdentifiant() {
        if (!$this->chaisid) {
            return $this->etablissement->identifiant;
        }
        return sprintf('%sC%02d', $this->etablissement->identifiant, $this->chaisid);
    }
    
    public function getLastHabilitationOrCreate() {
        return HabilitationClient::getInstance()->getLastHabilitationOrCreate($this->getEtablissement()->identifiant, $this->chaisid);
    }
    
}

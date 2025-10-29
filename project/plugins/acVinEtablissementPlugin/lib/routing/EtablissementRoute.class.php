<?php

class EtablissementRoute extends sfObjectRoute implements InterfaceEtablissementRoute {

    protected $etablissement = null;
    protected $campagne = null;
    protected $accesses = array();

    protected function getObjectForParameters($parameters = null) {
        $this->preGetObject($parameters);
        if (!$this->etablissement && isset($parameters['identifiant'])) {
            $this->etablissement = EtablissementClient::getInstance()->find($parameters['identifiant']);
        }
        if (!$this->etablissement) {
            throw new sfError403Exception("Vous n'avez pas le droit d'accéder à cette page (pas d'etablissement)");
        }
        $myUser = sfContext::getInstance()->getUser();
        $compteUser = $myUser->getCompte();
        if ($myUser->hasTeledeclaration() && !$myUser->hasDrevAdmin() &&
                $compteUser->identifiant != $this->getEtablissement()->getSociete()->getMasterCompte()->identifiant) {

            if ($myUser->getEtablissement()->hasCooperateur($this->getEtablissement()->cvi) === false) {
                throw new sfError403Exception("Vous n'avez pas le droit d'accéder à cette page (drevAdmin)");
            }
        }

        $allowed = $myUser->isAdmin();
        $allowed = $allowed || (isset($this->accesses['allow_habilitation']) && $this->accesses['allow_habilitation'] && $myUser->hasHabilitation());
        $allowed = $allowed || (isset($this->accesses['allow_admin_odg']) && $this->accesses['allow_admin_odg'] && $myUser->isAdminODG());

        if(!$allowed && ( $myUser->hasDrevAdmin() || $myUser->isAdminODG()) ) {
            $region = Organisme::getInstance()->getCurrentRegion();
            if(!$region || (!DrevConfiguration::getInstance()->hasHabilitationINAO() && !HabilitationClient::getInstance()->isRegionInHabilitation($this->etablissement->identifiant, $region))) {
                throw new sfError403RegionException($compteUser);
            }
            $allowed = true;
        }
        if (isset($this->accesses['allow_stalker']) && $this->accesses['allow_stalker']) {
            if ($myUser->isStalker()) {
                $region = Organisme::getInstance()->getCurrentRegion();
                if ($region) {
                    if (!DrevConfiguration::getInstance()->hasHabilitationINAO() && !HabilitationClient::getInstance()->isRegionInHabilitation($this->etablissement->identifiant, $region)) {
                        throw new sfError403RegionException($compteUser);
                    }
                }
                $allowed = true;
            }
        }

        if (!$allowed) {
            if ($myUser->hasTeledeclaration()) {
                $allowed = ($compteUser->identifiant == $this->getEtablissement()->getSociete()->getMasterCompte()->identifiant);
                $allowed = $allowed || $myUser->getEtablissement()->hasCooperateur($this->getEtablissement()->cvi) === true;
            }
        }
        if (!$allowed) {
            throw new sfError403Exception("Vous n'avez pas le droit d'accéder à cette page (Etablissement)");
        }
        $module = sfContext::getInstance()->getRequest()->getParameterHolder()->get('module');

        if($campagne = sfContext::getInstance()->getRequest()->getParameterHolder()->get('campagne',null)){
          $this->campagne = $campagne;
        }
        sfContext::getInstance()->getResponse()->setTitle(strtoupper($module).' - '.$this->etablissement->nom);
        return $this->etablissement;
    }

    protected function doConvertObjectToArray($object) {
        if (!$object) {
            throw new sfException("object from parameter should not be null");
        }
        return array("identifiant" => $object->getIdentifiant());
    }

    public function generate($params, $context = array(), $absolute = false)
    {
        if(sfContext::getInstance()->getRequest()->getParameter('coop') && !$this instanceof ParcellaireAffectationCoopRoute) {
            $params['coop'] = sfContext::getInstance()->getRequest()->getParameter('coop');
        }
        return parent::generate($params, $context, $absolute);
    }

    private function preGetObject($parameters) {
        if (is_array($parameters)) foreach($parameters as $k => $v) {
            if (strpos($k, 'allow') === false) {
                continue;
            }
            $this->accesses[$k] = $v;
        }
    }

    public function getEtablissement($parameters = null) {
        $this->preGetObject($parameters);
	    if (!$this->etablissement) {
            $this->getObject();
      	}

	    return $this->etablissement;
    }

    public function getCampagne(){
      return $this->campagne;
    }
}

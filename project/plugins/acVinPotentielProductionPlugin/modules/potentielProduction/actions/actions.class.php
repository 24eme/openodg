<?php

class potentielProductionActions extends sfActions {

    public function executeVisualisation(sfWebRequest $request) {
    	$this->etablissement = $this->getRoute()->getEtablissement();
    	$this->societe = $this->etablissement->getSociete();
        //$this->secureEtablissement(EtablissementSecurity::DECLARANT_POTENTIEL_PRODUCTION, $this->etablissement);
        $ppmanager = new PotentielProductionManager($this->etablissement->identifiant);
        $this->superficies = $ppmanager->getSuperficies();
        $this->donnees = $ppmanager->getDonnees();
    }

    protected function secure($droits, $doc) {
    	if (!ParcellaireSecurity::getInstance($this->getUser(), $doc)->isAuthorized($droits)) {

    		return $this->forwardSecure();
    	}
    }

    protected function secureEtablissement($droits, $etablissement) {
        if (!EtablissementSecurity::getInstance($this->getUser(), $etablissement)->isAuthorized($droits)) {

            return $this->forwardSecure();
        }
    }

    protected function forwardSecure() {
        $this->context->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

        throw new sfStopException();
    }

}

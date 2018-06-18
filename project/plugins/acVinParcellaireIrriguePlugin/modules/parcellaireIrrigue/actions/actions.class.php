<?php

class parcellaireIrrigueActions extends sfActions {

    public function executeIrrigation(sfWebRequest $request) {
    	$this->etablissement = $this->getRoute()->getEtablissement();
        $this->secureEtablissement(EtablissementSecurity::DECLARANT_PARCELLAIRE, $this->etablissement);
        
		$this->papier = $request->getParameter('papier', false);
		$this->campagne = $request->getParameter("campagne", ConfigurationClient::getInstance()->getCampagneManager()->getCurrent());
		
        $this->parcellaireIrrigue = ParcellaireIrrigueClient::getInstance()->createDoc($this->etablissement->identifiant, $this->campagne, $this->papier);
    
        $this->form = new ParcellaireIrrigueProduitsForm($this->parcellaireIrrigue);
        
        if (!$request->isMethod(sfWebRequest::POST)) {
        
        	return sfView::SUCCESS;
        }
        
        $this->form->bind($request->getParameter($this->form->getName()));
        
        if (!$this->form->isValid()) {
        
        	return sfView::SUCCESS;
        }
        
        $this->form->save();
        
        $this->getUser()->setFlash("notice", "Vos parcelles irriguées ont bien été enregistrées");
        
        return $this->redirect('parcellaireirrigue_edit', array('sf_subject' => $this->etablissement, 'campagne' => $this->campagne, 'papier' => $this->papier));
    
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


}

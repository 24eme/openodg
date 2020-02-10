<?php

class parcellaireAffectationActions extends sfActions {
    
    public function executeChoixDgc(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->secureEtablissement(EtablissementSecurity::DECLARANT_PARCELLAIRE, $this->etablissement);
    
        $this->papier = $request->getParameter('papier', false);
        $this->campagne = $request->getParameter("campagne", ConfigurationClient::getInstance()->getCampagneManager()->getCurrent() + 1);
        
        $this->parcellaireAffectation = ParcellaireAffectationClient::getInstance()->createDoc($this->etablissement->identifiant, $this->campagne, $this->papier);

        $this->form = new ParcellaireAffectationChoixDgcForm($this->parcellaireAffectation);

        if (!$request->isMethod(sfWebRequest::POST)) {

        	return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

        	return sfView::SUCCESS;
        }

        $obj = $this->form->save();

		$this->redirect('parcellaireAffectation_edit', array('sf_subject' => $this->etablissement, 'campagne' => $this->campagne, 'lieu' => $this->parcellaireAffectation->getNextDgc()));
    }

    public function executeAffectation(sfWebRequest $request) {
    	$this->etablissement = $this->getRoute()->getEtablissement();
        $this->secureEtablissement(EtablissementSecurity::DECLARANT_PARCELLAIRE, $this->etablissement);

        $this->lieu = $request->getParameter('lieu');
		$this->papier = $request->getParameter('papier', false);
		$this->campagne = $request->getParameter("campagne", ConfigurationClient::getInstance()->getCampagneManager()->getCurrent() + 1);
        
		$this->parcellaireAffectation = ParcellaireAffectationClient::getInstance()->getLast($this->etablissement->identifiant, $this->campagne);

		$this->form = new ParcellaireAffectationProduitsForm($this->parcellaireAffectation, $this->lieu);

        if (!$request->isMethod(sfWebRequest::POST)) {

        	return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

        	return sfView::SUCCESS;
        }

        $this->form->save();

        $this->getUser()->setFlash("notice", "Vos parcelles affectées ont bien été enregistrées");

        if ($nextLieu = $this->parcellaireAffectation->getNextDgc($this->lieu)) {
            return $this->redirect('parcellaireAffectation_edit', array('sf_subject' => $this->etablissement, 'campagne' => $this->campagne, 'papier' => $this->papier, 'lieu' => $nextLieu));
        } else {
            echo "vers la visu";exit; 
        }

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

    public function executePDF(sfWebRequest $request) {
        set_time_limit(180);
        $this->parcellaireAffectation = $this->getRoute()->getParcellaireAffectation();
        $this->secure(ParcellaireSecurity::VISUALISATION, $this->parcellaireAffectation);


        $this->document = new ExportParcellaireAffectationPDF($this->parcellaireAffectation, $this->getRequestParameter('output', 'pdf'), false);
        $this->document->setPartialFunction(array($this, 'getPartial'));

        if ($request->getParameter('force')) {
            $this->document->removeCache();
        }

        $this->document->generate();

        $this->document->addHeaders($this->getResponse());

        return $this->renderText($this->document->output());
    }


}

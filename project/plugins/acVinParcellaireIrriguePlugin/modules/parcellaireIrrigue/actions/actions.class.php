<?php

class parcellaireIrrigueActions extends sfActions {

    public function executeIrrigation(sfWebRequest $request) {
    	$this->etablissement = $this->getRoute()->getEtablissement();
        $this->secureEtablissement(EtablissementSecurity::DECLARANT_PARCELLAIRE, $this->etablissement);

		$this->papier = $request->getParameter('papier', false);
		$this->campagne = $request->getParameter("campagne", ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_PREMIERE_ANNEE)->getCurrent() + 1);

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

    public function executePDF(sfWebRequest $request) {
        set_time_limit(180);
        $this->parcellaireIrrigue = $this->getRoute()->getParcellaireIrrigue();
        $this->secure(ParcellaireSecurity::VISUALISATION, $this->parcellaireIrrigue);


        $this->document = new ExportParcellaireIrriguePDF($this->parcellaireIrrigue, $this->getRequestParameter('output', 'pdf'), false);
        $this->document->setPartialFunction(array($this, 'getPartial'));

        if ($request->getParameter('force')) {
            $this->document->removeCache();
        }

        $this->document->generate();

        $this->document->addHeaders($this->getResponse());

        return $this->renderText($this->document->output());
    }


}

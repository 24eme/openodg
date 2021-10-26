<?php

class parcellaireIrrigableActions extends sfActions {

    public function executeCreate(sfWebRequest $request) {
    	$etablissement = $this->getRoute()->getEtablissement();
        $this->secureEtablissement(EtablissementSecurity::DECLARANT_PARCELLAIRE, $etablissement);

        $periode = $request->getParameter("periode", ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_PREMIERE_ANNEE)->getCurrent() * 1);
        $parcellaireIrrigable = ParcellaireIrrigableClient::getInstance()->createDoc($etablissement->identifiant, $periode);
        $parcellaireIrrigable->save();

        return $this->redirect('parcellaireirrigable_edit', $parcellaireIrrigable);
    }

    public function executeCreatePapier(sfWebRequest $request) {
    	$etablissement = $this->getRoute()->getEtablissement();
        $this->secureEtablissement(EtablissementSecurity::DECLARANT_PARCELLAIRE, $etablissement);

        $periode = $request->getParameter("periode", ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_PREMIERE_ANNEE)->getCurrent() * 1);
        $parcellaireIrrigable = ParcellaireIrrigableClient::getInstance()->createDoc($etablissement->identifiant, $periode, true);
        $parcellaireIrrigable->save();

        return $this->redirect('parcellaireirrigable_edit', $parcellaireIrrigable);
    }

    public function executeEdit(sfWebRequest $request) {
    	$parcellaireIrrigable = $this->getRoute()->getParcellaireIrrigable();

    	$this->secure(ParcellaireSecurity::EDITION, $parcellaireIrrigable);

    	if ($parcellaireIrrigable->exist('etape') && $parcellaireIrrigable->etape) {
    		return $this->redirect('parcellaireirrigable_' . $parcellaireIrrigable->etape, $parcellaireIrrigable);
    	}

    	return $this->redirect('parcellaireirrigable_exploitation', $parcellaireIrrigable);
    }
    public function executeDelete(sfWebRequest $request) {
    	$parcellaireIrrigable = $this->getRoute()->getParcellaireIrrigable();
    	$etablissement = $parcellaireIrrigable->getEtablissementObject();
    	$this->secure(ParcellaireSecurity::EDITION, $parcellaireIrrigable);

    	$parcellaireIrrigable->delete();
    	$this->getUser()->setFlash("notice", "La déclaration a été supprimée avec succès.");

    	return $this->redirect('declaration_etablissement', array('identifiant' => $etablissement->identifiant, 'campagne' => $parcellaireIrrigable->campagne));
    }

    public function executeDevalidation(sfWebRequest $request) {
    	$parcellaireIrrigable = $this->getRoute()->getParcellaireIrrigable();
    	if (!$this->getUser()->isAdmin()) {
    		$this->secure(ParcellaireSecurity::DEVALIDATION , $parcellaireIrrigable);
    	}

    	$parcellaireIrrigable->validation = null;
    	$parcellaireIrrigable->validation_odg = null;
    	$parcellaireIrrigable->add('etape', null);
    	$parcellaireIrrigable->save();

    	$this->getUser()->setFlash("notice", "La déclaration a été dévalidé avec succès.");

    	return $this->redirect($this->generateUrl('parcellaireirrigable_edit', $parcellaireIrrigable));
    }

    public function executeExploitation(sfWebRequest $request) {
    	$this->parcellaireIrrigable = $this->getRoute()->getParcellaireIrrigable();
    	$this->secure(ParcellaireSecurity::EDITION, $this->parcellaireIrrigable);

    	if($this->parcellaireIrrigable->storeEtape($this->getEtape($this->parcellaireIrrigable, ParcellaireIrrigableEtapes::ETAPE_EXPLOITATION))) {
    		$this->parcellaireIrrigable->save();
    	}

    	$this->etablissement = $this->parcellaireIrrigable->getEtablissementObject();

    	$this->form = new EtablissementForm($this->parcellaireIrrigable->declarant, array("use_email" => !$this->parcellaireIrrigable->isPapier()));

    	if (!$request->isMethod(sfWebRequest::POST)) {

    		return sfView::SUCCESS;
    	}

    	$this->form->bind($request->getParameter($this->form->getName()));

    	if (!$this->form->isValid()) {

    		return sfView::SUCCESS;
    	}

    	$this->form->save();

    	if ($this->form->hasUpdatedValues() && !$this->parcellaireIrrigable->isPapier()) {
    		Email::getInstance()->sendNotificationModificationsExploitation($this->parcellaireIrrigable->getEtablissementObject(), $this->form->getUpdatedValues());
    	}

    	if ($request->isXmlHttpRequest()) {

    		return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->etablissement->_id, "revision" => $this->etablissement->_rev))));
    	}

    	if ($request->getParameter('redirect', null)) {
    		return $this->redirect('parcellaireirrigable_validation', $this->parcellaireIrrigable);
    	}

    	return $this->redirect('parcellaireirrigable_parcelles', $this->parcellaireIrrigable);
    }

    public function executeParcelles(sfWebRequest $request) {
    	$this->parcellaireIrrigable = $this->getRoute()->getParcellaireIrrigable();
    	$this->secure(ParcellaireSecurity::EDITION, $this->parcellaireIrrigable);

    	if($this->parcellaireIrrigable->storeEtape($this->getEtape($this->parcellaireIrrigable, ParcellaireIrrigableEtapes::ETAPE_PARCELLES))) {
    		$this->parcellaireIrrigable->save();
    	}

    	$this->etablissement = $this->parcellaireIrrigable->getEtablissementObject();

    	if (!$request->isMethod(sfWebRequest::POST)) {

    		return sfView::SUCCESS;
    	}

    	$this->parcellaireIrrigable->addParcellesFromParcellaire($request->getPostParameter('parcelles', array()));

    	$this->parcellaireIrrigable->save();

        if($request->getParameter('saveandquit')) {

            return $this->redirect('declaration_etablissement', $this->parcellaireIrrigable->getEtablissementObject());
        }

    	return $this->redirect('parcellaireirrigable_irrigations', $this->parcellaireIrrigable);
    }

    public function executeIrrigations(sfWebRequest $request) {
    	$this->parcellaireIrrigable = $this->getRoute()->getParcellaireIrrigable();
    	$this->secure(ParcellaireSecurity::EDITION, $this->parcellaireIrrigable);

    	if($this->parcellaireIrrigable->storeEtape($this->getEtape($this->parcellaireIrrigable, ParcellaireIrrigableEtapes::ETAPE_IRRIGATIONS))) {
    		$this->parcellaireIrrigable->save();
    	}

    	$this->etablissement = $this->parcellaireIrrigable->getEtablissementObject();

    	$this->form = new ParcellaireIrrigableProduitsForm($this->parcellaireIrrigable);

    	if (!$request->isMethod(sfWebRequest::POST)) {

    		return sfView::SUCCESS;
    	}

    	$this->form->bind($request->getParameter($this->form->getName()));

    	if (!$this->form->isValid()) {

    		return sfView::SUCCESS;
    	}

    	$this->form->save();

        if($request->getParameter('saveandquit')) {

            return $this->redirect('declaration_etablissement', $this->parcellaireIrrigable->getEtablissementObject());
        }

    	return $this->redirect('parcellaireirrigable_validation', $this->parcellaireIrrigable);
    }


    public function executeValidation(sfWebRequest $request) {
    	$this->parcellaireIrrigable = $this->getRoute()->getParcellaireIrrigable();
    	$this->secure(ParcellaireSecurity::EDITION, $this->parcellaireIrrigable);

    	if($this->parcellaireIrrigable->storeEtape($this->getEtape($this->parcellaireIrrigable, ParcellaireIrrigableEtapes::ETAPE_VALIDATION))) {
    		$this->parcellaireIrrigable->save();
    	}

		if($this->getUser()->isAdmin()) {
	       	$this->parcellaireIrrigable->validateOdg();
	    }

    	$this->form = new ParcellaireIrrigableValidationForm($this->parcellaireIrrigable);

    	if (!$request->isMethod(sfWebRequest::POST)) {
    		$this->validation = new ParcellaireIrrigableValidation($this->parcellaireIrrigable);
    		return sfView::SUCCESS;
    	}

    	$this->form->bind($request->getParameter($this->form->getName()));

    	if (!$this->form->isValid()) {

    		return sfView::SUCCESS;
    	}

    	$this->form->save();

    	$this->getUser()->setFlash("notice", "Vos parcelles irrigables ont bien été enregistrées");
    	return $this->redirect('parcellaireirrigable_visualisation', $this->parcellaireIrrigable);
    }

    public function executePDF(sfWebRequest $request) {
    	set_time_limit(180);
    	$this->parcellaireIrrigable = $this->getRoute()->getParcellaireIrrigable();
    	$this->secure(ParcellaireSecurity::VISUALISATION, $this->parcellaireIrrigable);


    	$this->document = new ExportParcellaireIrrigablePDF($this->parcellaireIrrigable, $this->getRequestParameter('output', 'pdf'), false);
    	$this->document->setPartialFunction(array($this, 'getPartial'));

    	if ($request->getParameter('force')) {
    		$this->document->removeCache();
    	}

    	$this->document->generate();

    	$this->document->addHeaders($this->getResponse());

    	return $this->renderText($this->document->output());
    }


    public function executeVisualisation(sfWebRequest $request) {
    	$this->parcellaireIrrigable = $this->getRoute()->getParcellaireIrrigable();
    	$this->secure(ParcellaireSecurity::VISUALISATION, $this->parcellaireIrrigable);
    }


    protected function getEtape($parcellaireIrrigable, $etape) {
    	$parcellaireIrrigableEtapes = ParcellaireIrrigableEtapes::getInstance();
    	if (!$parcellaireIrrigableEtapes->exist('etape')) {
    		return $etape;
    	}
    	return ($parcellaireIrrigableEtapes->isLt($parcellaireIrrigableEtapes->etape, $etape)) ? $etape : $parcellaireIrrigableEtapes->etape;
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

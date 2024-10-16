<?php

class parcellaireManquantActions extends sfActions {

    public function executeCreate(sfWebRequest $request) {
    	$etablissement = $this->getRoute()->getEtablissement();
        $this->secureEtablissement(EtablissementSecurity::DECLARANT_PARCELLAIRE, $etablissement);

        $periode = $request->getParameter("periode", ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_PREMIERE_ANNEE)->getCurrent() * 1);
        $parcellaireManquant = ParcellaireManquantClient::getInstance()->createDocFromPrevious($etablissement->identifiant, $periode);
        $parcellaireManquant->save();

        return $this->redirect('parcellairemanquant_edit', $parcellaireManquant);
    }

    public function executeCreatePapier(sfWebRequest $request) {
    	$etablissement = $this->getRoute()->getEtablissement();
        $this->secureEtablissement(EtablissementSecurity::DECLARANT_PARCELLAIRE, $etablissement);

        $periode = $request->getParameter("periode", ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_PREMIERE_ANNEE)->getCurrent() * 1);
        $parcellaireManquant = ParcellaireManquantClient::getInstance()->createDocFromPrevious($etablissement->identifiant, $periode, true);
        $parcellaireManquant->save();

        return $this->redirect('parcellairemanquant_edit', $parcellaireManquant);
    }

    public function executeEdit(sfWebRequest $request) {
    	$parcellaireManquant = $this->getRoute()->getParcellaireManquant();

    	$this->secure(ParcellaireSecurity::EDITION, $parcellaireManquant);

    	if ($parcellaireManquant->exist('etape') && $parcellaireManquant->etape) {
    		return $this->redirect('parcellairemanquant_' . $parcellaireManquant->etape, $parcellaireManquant);
    	}

        if($request->getParameter('coop')) {

            return $this->redirect('parcellairemanquant_parcelles', $parcellaireManquant);
        }

    	return $this->redirect('parcellairemanquant_exploitation', $parcellaireManquant);
    }
    public function executeDelete(sfWebRequest $request) {
    	$parcellaireManquant = $this->getRoute()->getParcellaireManquant();
    	$etablissement = $parcellaireManquant->getEtablissementObject();
    	$this->secure(ParcellaireSecurity::EDITION, $parcellaireManquant);

    	$parcellaireManquant->delete();
    	$this->getUser()->setFlash("notice", "La déclaration a été supprimée avec succès.");

        return $this->redirect('declaration_etablissement', array('identifiant' => $etablissement->identifiant, 'campagne' => $parcellaireManquant->campagne));
    }

    public function executeDevalidation(sfWebRequest $request) {
    	$parcellaireManquant = $this->getRoute()->getParcellaireManquant();
    	if (!$this->getUser()->isAdmin()) {
    		$this->secure(ParcellaireSecurity::DEVALIDATION , $parcellaireManquant);
    	}

    	$parcellaireManquant->validation = null;
    	$parcellaireManquant->validation_odg = null;
    	$parcellaireManquant->add('etape', null);
    	$parcellaireManquant->save();

    	$this->getUser()->setFlash("notice", "La déclaration a été dévalidé avec succès.");

    	return $this->redirect($this->generateUrl('parcellairemanquant_edit', $parcellaireManquant));
    }

    public function executeExploitation(sfWebRequest $request) {
    	$this->parcellaireManquant = $this->getRoute()->getParcellaireManquant();
        $this->coop = $request->getParameter('coop');
    	$this->secure(ParcellaireSecurity::EDITION, $this->parcellaireManquant);

    	if($this->parcellaireManquant->storeEtape($this->getEtape($this->parcellaireManquant, ParcellaireManquantEtapes::ETAPE_EXPLOITATION))) {
    		$this->parcellaireManquant->save();
    	}

    	$this->etablissement = $this->parcellaireManquant->getEtablissementObject();

    	$this->form = new EtablissementForm($this->parcellaireManquant->declarant, array("use_email" => !$this->parcellaireManquant->isPapier()));

    	if (!$request->isMethod(sfWebRequest::POST)) {

    		return sfView::SUCCESS;
    	}

    	$this->form->bind($request->getParameter($this->form->getName()));

    	if (!$this->form->isValid()) {

    		return sfView::SUCCESS;
    	}

    	$this->form->save();

    	if ($this->form->hasUpdatedValues() && !$this->parcellaireManquant->isPapier()) {
    		Email::getInstance()->sendNotificationModificationsExploitation($this->parcellaireManquant->getEtablissementObject(), $this->form->getUpdatedValues());
    	}

    	if ($request->isXmlHttpRequest()) {

    		return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->etablissement->_id, "revision" => $this->etablissement->_rev))));
    	}

    	if ($request->getParameter('redirect', null)) {
    		return $this->redirect('parcellairemanquant_validation', $this->parcellaireManquant);
    	}

    	return $this->redirect('parcellairemanquant_parcelles', $this->parcellaireManquant);
    }

    public function executeParcelles(sfWebRequest $request) {
    	$this->parcellaireManquant = $this->getRoute()->getParcellaireManquant();
        $this->coop = $request->getParameter('coop');
    	$this->secure(ParcellaireSecurity::EDITION, $this->parcellaireManquant);

    	if($this->parcellaireManquant->storeEtape($this->getEtape($this->parcellaireManquant, ParcellaireManquantEtapes::ETAPE_PARCELLES))) {
    		$this->parcellaireManquant->save();
    	}

    	$this->etablissement = $this->parcellaireManquant->getEtablissementObject();

    	if (!$request->isMethod(sfWebRequest::POST)) {

    		return sfView::SUCCESS;
    	}

    	$this->parcellaireManquant->setParcellesFromParcellaire($request->getPostParameter('parcelles', array()));

    	$this->parcellaireManquant->save();

        if($request->getParameter('saveandquit')) {

            return $this->redirect('declaration_etablissement', $this->parcellaireManquant->getEtablissementObject());
        }

    	return $this->redirect('parcellairemanquant_manquants', $this->parcellaireManquant);
    }

    public function executeManquants(sfWebRequest $request) {
    	$this->parcellaireManquant = $this->getRoute()->getParcellaireManquant();
        $this->coop = $request->getParameter('coop');
    	$this->secure(ParcellaireSecurity::EDITION, $this->parcellaireManquant);

    	if($this->parcellaireManquant->storeEtape($this->getEtape($this->parcellaireManquant, ParcellaireManquantEtapes::ETAPE_SAISIEINFOS))) {
    		$this->parcellaireManquant->save();
    	}

    	$this->etablissement = $this->parcellaireManquant->getEtablissementObject();

    	$this->form = new ParcellaireManquantInfosForm($this->parcellaireManquant);

    	if (!$request->isMethod(sfWebRequest::POST)) {

    		return sfView::SUCCESS;
    	}

    	$this->form->bind($request->getParameter($this->form->getName()));

    	if (!$this->form->isValid()) {

    		return sfView::SUCCESS;
    	}

    	$this->form->save();

        if($request->getParameter('saveandquit')) {

            return $this->redirect('declaration_etablissement', $this->parcellaireManquant->getEtablissementObject());
        }

    	return $this->redirect('parcellairemanquant_validation', $this->parcellaireManquant);
    }


    public function executeValidation(sfWebRequest $request) {
    	$this->parcellaireManquant = $this->getRoute()->getParcellaireManquant();
        $this->coop = $request->getParameter('coop');
    	$this->secure(ParcellaireSecurity::EDITION, $this->parcellaireManquant);

    	if($this->parcellaireManquant->storeEtape($this->getEtape($this->parcellaireManquant, ParcellaireManquantEtapes::ETAPE_VALIDATION))) {
    		$this->parcellaireManquant->save();
    	}

		if($this->getUser()->isAdmin()) {
	       	$this->parcellaireManquant->validateOdg();
	    }

        $this->validation = new ParcellaireManquantValidation($this->parcellaireManquant);

        $this->form = new ParcellaireManquantValidationForm($this->parcellaireManquant, ['engagements' => $this->validation->getEngagements()]);

    	if (!$request->isMethod(sfWebRequest::POST)) {
    		return sfView::SUCCESS;
    	}

    	$this->form->bind($request->getParameter($this->form->getName()));

    	if (!$this->form->isValid()) {

    		return sfView::SUCCESS;
    	}

    	$this->form->save();

    	$this->getUser()->setFlash("notice", "Vos informations ont bien été enregistrées");
    	return $this->redirect('parcellairemanquant_visualisation', $this->parcellaireManquant);
    }

    public function executePDF(sfWebRequest $request) {
    	set_time_limit(180);
        $this->parcellaireManquant = $this->getRoute()->getParcellaireManquant(['allow_habilitation' => true, 'allow_stalker' => true]);
    	$this->secure(ParcellaireSecurity::VISUALISATION, $this->parcellaireManquant);


    	$this->document = new ExportParcellaireManquantPDF($this->parcellaireManquant, $this->getRequestParameter('output', 'pdf'), false);
    	$this->document->setPartialFunction(array($this, 'getPartial'));

    	if ($request->getParameter('force')) {
    		$this->document->removeCache();
    	}

    	$this->document->generate();

    	$this->document->addHeaders($this->getResponse());

    	return $this->renderText($this->document->output());
    }


    public function executeVisualisation(sfWebRequest $request) {
    	$this->parcellaireManquant = $this->getRoute()->getParcellaireManquant();
        $this->coop = $request->getParameter('coop');
    	$this->secure(ParcellaireSecurity::VISUALISATION, $this->parcellaireManquant);
    }


    protected function getEtape($parcellaireManquant, $etape) {
    	$parcellaireManquantEtapes = ParcellaireManquantEtapes::getInstance();
    	if (!$parcellaireManquantEtapes->exist('etape')) {
    		return $etape;
    	}
    	return ($parcellaireManquantEtapes->isLt($parcellaireManquantEtapes->etape, $etape)) ? $etape : $parcellaireManquantEtapes->etape;
    }

    protected function secure($droits, $doc) {
        if ($this->getUser()->isAdminODG()) {
            return ;
        }
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

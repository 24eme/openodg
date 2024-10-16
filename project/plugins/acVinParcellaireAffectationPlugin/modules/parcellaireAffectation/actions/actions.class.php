<?php

class parcellaireAffectationActions extends sfActions {

    public function executeCreate(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();
        $this->secureEtablissement(EtablissementSecurity::DECLARANT_PARCELLAIRE, $etablissement);

        $periode = $request->getParameter("periode", ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_PREMIERE_ANNEE)->getCurrent() * 1);
        $parcellaireAffectation = ParcellaireAffectationClient::getInstance()->findOrCreate($etablissement->identifiant, $periode);
        $parcellaireAffectation->save();

        return $this->redirect('parcellaireaffectation_edit', $parcellaireAffectation);
    }

    public function executeCreatePapier(sfWebRequest $request) {
    	$etablissement = $this->getRoute()->getEtablissement();
        $this->secureEtablissement(EtablissementSecurity::DECLARANT_PARCELLAIRE, $etablissement);

        $periode = $request->getParameter("periode", ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_PREMIERE_ANNEE)->getCurrent() * 1);
        $parcellaireAffectation = ParcellaireAffectationClient::getInstance()->createDoc($etablissement->identifiant, $periode, true);
        $parcellaireAffectation->save();

        return $this->redirect('parcellaireaffectation_edit', $parcellaireAffectation);
    }

    public function executeEdit(sfWebRequest $request) {
    	$parcellaireAffectation = $this->getRoute()->getParcellaireAffectation();

    	$this->secure(ParcellaireSecurity::EDITION, $parcellaireAffectation);

    	if ($parcellaireAffectation->exist('etape') && $parcellaireAffectation->etape) {
    		return $this->redirect('parcellaireaffectation_' . $parcellaireAffectation->etape, $parcellaireAffectation);
    	}

        if($request->getParameter('coop')) {

            return $this->redirect('parcellaireaffectation_affectations', $parcellaireAffectation);
        }

    	return $this->redirect('parcellaireaffectation_exploitation', $parcellaireAffectation);
    }
    public function executeDelete(sfWebRequest $request) {
    	$parcellaireAffectation = $this->getRoute()->getParcellaireAffectation();
    	$etablissement = $parcellaireAffectation->getEtablissementObject();
    	$this->secure(ParcellaireSecurity::EDITION, $parcellaireAffectation);

    	$parcellaireAffectation->delete();
    	$this->getUser()->setFlash("notice", "La déclaration a été supprimée avec succès.");

        return $this->redirect('declaration_etablissement', array('identifiant' => $etablissement->identifiant, 'campagne' => $parcellaireAffectation->campagne));
    }

    public function executeDevalidation(sfWebRequest $request) {
    	$parcellaireAffectation = $this->getRoute()->getParcellaireAffectation();
    	if (!$this->getUser()->isAdmin()) {
    		$this->secure(ParcellaireSecurity::DEVALIDATION , $parcellaireAffectation);
    	}

    	$parcellaireAffectation->devalidate();
    	$parcellaireAffectation->save();

    	$this->getUser()->setFlash("notice", "La déclaration a été dévalidée avec succès.");

    	return $this->redirect($this->generateUrl('parcellaireaffectation_edit', $parcellaireAffectation));
    }

    public function executeExploitation(sfWebRequest $request) {
    	$this->parcellaireAffectation = $this->getRoute()->getParcellaireAffectation();
        $this->coop = $request->getParameter('coop');
        $this->secure(ParcellaireSecurity::EDITION, $this->parcellaireAffectation);
    	if($this->parcellaireAffectation->storeEtape($this->getEtape($this->parcellaireAffectation, ParcellaireAffectationEtapes::ETAPE_EXPLOITATION))) {
    		$this->parcellaireAffectation->save();
    	}

    	$this->etablissement = $this->parcellaireAffectation->getEtablissementObject();

    	$this->form = new EtablissementForm($this->parcellaireAffectation->declarant, array("use_email" => !$this->parcellaireAffectation->isPapier()));

    	if (!$request->isMethod(sfWebRequest::POST)) {

    		return sfView::SUCCESS;
    	}

    	$this->form->bind($request->getParameter($this->form->getName()));

    	if (!$this->form->isValid()) {

    		return sfView::SUCCESS;
    	}

    	$this->form->save();

    	if ($this->form->hasUpdatedValues() && !$this->parcellaireAffectation->isPapier()) {
    		Email::getInstance()->sendNotificationModificationsExploitation($this->parcellaireAffectation->getEtablissementObject(), $this->form->getUpdatedValues());
    	}

    	if ($request->isXmlHttpRequest()) {

    		return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->etablissement->_id, "revision" => $this->etablissement->_rev))));
    	}

    	if ($request->getParameter('redirect', null)) {
    		return $this->redirect('parcellaireaffectation_validation', $this->parcellaireAffectation);
    	}

    	return $this->redirect('parcellaireaffectation_affectations', $this->parcellaireAffectation);
    }

    public function executeAffectations(sfWebRequest $request) {
        $this->parcellaireAffectation = $this->getRoute()->getParcellaireAffectation();
        $this->coop = $request->getParameter('coop');
        $this->destinataire = $request->getParameter('destinataire', $this->parcellaireAffectation->getEtablissementObject()->_id);
        $this->secure(ParcellaireSecurity::EDITION, $this->parcellaireAffectation);

        if ($this->coop) {
            $coop_id = explode('-', $this->coop)[1];
            if (strpos($this->destinataire, $coop_id) === false) {
                return $this->redirect('parcellaireaffectation_affectations', ['sf_subject' => $this->parcellaireAffectation, 'destinataire' => 'ETABLISSEMENT-'.$coop_id]);
            }
        }

        $this->parcellaireAffectation->updateParcellesAffectation();
    	if($this->parcellaireAffectation->storeEtape($this->getEtape($this->parcellaireAffectation, ParcellaireAffectationEtapes::ETAPE_AFFECTATIONS))) {
    		$this->parcellaireAffectation->save();
    	}

    	$this->etablissement = $this->parcellaireAffectation->getEtablissementObject();

		$this->form = new ParcellaireAffectationProduitsForm($this->parcellaireAffectation, $this->destinataire);

        $this->destinataires = $this->parcellaireAffectation->getDestinataires();

        if (!$request->isMethod(sfWebRequest::POST)) {

        	return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

        	return sfView::SUCCESS;
        }

        $this->form->save();

        if($request->getParameter('saveandquit')) {

            return $this->redirect('declaration_etablissement', $this->parcellaireAffectation->getEtablissementObject());
        }

        $finded = false;
        $previous = null;
        if (!$this->coop) foreach($this->destinataires as $dId => $d) {
            if($dId == $this->destinataire && $request->getParameter('previous')) {
                break;
            }
            $previous = $dId;
            if($finded) {
                return $this->redirect('parcellaireaffectation_affectations', ['sf_subject' => $this->parcellaireAffectation, 'destinataire' => $dId]);
            }
            if($dId == $this->destinataire && !$request->getParameter('previous')) {
                $finded = true;
            }


        }
        if($request->getParameter('previous') && $previous) {
            return $this->redirect('parcellaireaffectation_affectations', ['sf_subject' => $this->parcellaireAffectation, 'destinataire' => $previous]);
        }

        if($request->getParameter('previous')) {
            $this->redirect('parcellaireaffectation_exploitation', ['sf_subject' => $this->parcellaireAffectation]);
        }

        return $this->redirect('parcellaireaffectation_validation', ['sf_subject' => $this->parcellaireAffectation]);

    }

    public function executeValidation(sfWebRequest $request) {
    	$this->parcellaireAffectation = $this->getRoute()->getParcellaireAffectation();
        $this->coop = $request->getParameter('coop');
        $this->secure(ParcellaireSecurity::EDITION, $this->parcellaireAffectation);

    	if($this->parcellaireAffectation->storeEtape($this->getEtape($this->parcellaireAffectation, ParcellaireAffectationEtapes::ETAPE_VALIDATION))) {
    		$this->parcellaireAffectation->save();
    	}

		if($this->getUser()->isAdmin()) {
	       	$this->parcellaireAffectation->validateOdg();
	    }

    	$this->form = new ParcellaireAffectationValidationForm($this->parcellaireAffectation);

    	if (!$request->isMethod(sfWebRequest::POST)) {
    		$this->validation = new ParcellaireAffectationValidation($this->parcellaireAffectation);
    		return sfView::SUCCESS;
    	}

    	$this->form->bind($request->getParameter($this->form->getName()));

    	if (!$this->form->isValid()) {

    		return sfView::SUCCESS;
    	}

    	$this->form->save();

    	$this->getUser()->setFlash("notice", "Vos affectations ont bien été enregistrées");
    	return $this->redirect('parcellaireaffectation_visualisation', $this->parcellaireAffectation);
    }

    public function executePDF(sfWebRequest $request) {
    	set_time_limit(180);
        $this->parcellaireAffectation = $this->getRoute()->getParcellaireAffectation(['allow_habilitation' => true, 'allow_stalker' => true]);
        $this->parcellaireAffectation->cleanNonAffectee();
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


    public function executeVisualisation(sfWebRequest $request) {
    	$this->parcellaireAffectation = $this->getRoute()->getParcellaireAffectation();
        $this->parcellaireAffectation->cleanNonAffectee();
        $this->coop = $request->getParameter('coop');
    	$this->secure(ParcellaireSecurity::VISUALISATION, $this->parcellaireAffectation);
    }


    protected function getEtape($parcellaireAffectation, $etape) {
    	$parcellaireAffectationEtapes = ParcellaireAffectationEtapes::getInstance();
    	if (!$parcellaireAffectationEtapes->exist('etape')) {
    		return $etape;
    	}
    	return ($parcellaireAffectationEtapes->isLt($parcellaireAffectationEtapes->etape, $etape)) ? $etape : $parcellaireAffectationEtapes->etape;
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

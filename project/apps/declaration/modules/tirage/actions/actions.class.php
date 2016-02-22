<?php

class tirageActions extends sfActions {

    public function executeCreate(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();
        $tirage = TirageClient::getInstance()->createDoc($etablissement->identifiant, ConfigurationClient::getInstance()->getCampagneManager()->getCurrent());
        $tirage->save();

        return $this->redirect('tirage_edit', $tirage);
    }

    public function executeCreatePapier(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();

        $tirage = TirageClient::getInstance()->createDoc($etablissement->identifiant, ConfigurationClient::getInstance()->getCampagneManager()->getCurrent());
        $tirage->save();

        return $this->redirect('drevmarc_edit', $tirage);
    }

    public function executeEdit(sfWebRequest $request) {
        $tirage = $this->getRoute()->getTirage();

        if ($tirage->exist('etape') && $tirage->etape) {
            return $this->redirect('tirage_' . $tirage->etape, $tirage);
        }

        return $this->redirect('tirage_exploitation', $tirage);
    }

    public function executeDevalidation(sfWebRequest $request) {
        $tirage = $this->getRoute()->getTirage();

        $this->secure(TirageSecurity::DEVALIDATION, $tirage);

        $tirage->validation = null;
        $tirage->validation_odg = null;
        $tirage->save();

        $this->getUser()->setFlash("notice", "La déclaration a été dévalidé avec succès.");

        return $this->redirect($this->generateUrl('home'));
    }

    public function executeDelete(sfWebRequest $request) {
        $tirage = $this->getRoute()->getTirage();
        $tirage->delete();
        $this->getUser()->setFlash("notice", 'La déclaration de tirage a été supprimé avec succès.');

        return $this->redirect($this->generateUrl('home'));
    }

    public function executeExploitation(sfWebRequest $request) {
        $this->tirage = $this->getRoute()->getTirage();

        $this->secure(TirageSecurity::EDITION, $this->tirage);

        $this->tirage->storeEtape($this->getEtape($this->tirage, TirageEtapes::ETAPE_EXPLOITATION));

        $this->tirage->save();

        $this->etablissement = $this->tirage->getEtablissementObject();

        $this->form = new EtablissementForm($this->etablissement, array("use_email" => !$this->tirage->isPapier()));

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        $this->tirage->storeDeclarant();
        $this->tirage->save();

        return $this->redirect('tirage_vin', $this->tirage);
    }

    public function executeVin(sfWebRequest $request) {

        $this->tirage = $this->getRoute()->getTirage();
        $this->form = new TirageVinForm($this->tirage);
        if (!$request->isMethod(sfWebRequest::POST)) {
            return sfView::SUCCESS;
        }


        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {
            return sfView::SUCCESS;
        }

        $this->form->save();
          return $this->redirect('tirage_lots', $this->tirage);
    }

    public function executeLots(sfWebRequest $request) 
    {
		$this->tirage = $this->getRoute()->getTirage();
		$this->secure(TirageSecurity::EDITION, $this->tirage);

        $this->tirage->storeEtape($this->getEtape($this->tirage, TirageEtapes::ETAPE_LOTS));

        $this->tirage->save();
		
		$this->form = new TirageLotsForm($this->tirage);
		
		if (!$request->isMethod(sfWebRequest::POST)) {
		
			return sfView::SUCCESS;
		}
		
		$this->form->bind($request->getParameter($this->form->getName()));
		
		if (!$this->form->isValid()) {
		
			return sfView::SUCCESS;
		}
		
		$this->form->save();
		
		return $this->redirect('tirage_validation', $this->tirage);
          
    }

    public function executeValidation(sfWebRequest $request) {
        $this->tirage = $this->getRoute()->getTirage();

        $this->secure(TirageSecurity::EDITION, $this->tirage);

        $this->tirage->storeEtape($this->getEtape($this->tirage, DrevEtapes::ETAPE_VALIDATION));
        $this->tirage->save();

        $this->validation = new TirageValidation($this->tirage);

        $this->form = new TirageValidationForm($this->tirage);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        if (!$this->validation->isValide()) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        if ($this->tirage->isPapier()) {
            $this->getUser()->setFlash("notice", "La déclaration a bien été validée");

            $this->tirage->validate($this->form->getValue("date"));
            $this->tirage->validateOdg();
            $this->tirage->save();

            return $this->redirect('tirage_visualisation', $this->tirage);
        }

        $this->tirage->validate();
        $this->tirage->save();

        //$this->sendDRevMarcValidation($this->tirage);

        return $this->redirect('tirage_confirmation', $this->tirage);
    }

    public function executeConfirmation(sfWebRequest $request) {
        $this->tirage = $this->getRoute()->getTirage();
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->tirage = $this->getRoute()->getTirage();
    }

    public function executePDF(sfWebRequest $request) 
    {
        $tirage = $this->getRoute()->getTirage();
        $this->secure(TirageSecurity::VISUALISATION, $tirage);

        if (!$tirage->validation) {
            //    $tirage->cleanDoc();
        }

        $this->document = new ExportTiragePdf($tirage, $this->getRequestParameter('output', 'pdf'), false);
        $this->document->setPartialFunction(array($this, 'getPartial'));

        if ($request->getParameter('force')) {
            $this->document->removeCache();
        }

        $this->document->generate();

        $this->document->addHeaders($this->getResponse());

        return $this->renderText($this->document->output());
    }

    protected function getEtape($tirage, $etape) {
        $tirageEtapes = TirageEtapes::getInstance();
        if (!$tirage->exist('etape')) {
            return $etape;
        }
        return ($tirageEtapes->isLt($tirage->etape, $etape)) ? $etape : $tirage->etape;
    }

    protected function secure($droits, $doc) {
        if (!TirageSecurity::getInstance($this->getUser(), $doc)->isAuthorized($droits)) {
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

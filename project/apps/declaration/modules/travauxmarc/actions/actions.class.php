<?php

class travauxmarcActions extends sfActions {

    public function executeCreate(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();
        $this->secureEtablissement($etablissement);
        $travaumarc = TravauxMarcClient::getInstance()->createDoc($etablissement->identifiant, $request->getParameter('campagne', ConfigurationClient::getInstance()->getCampagneManager()->getCurrent()));
        $travaumarc->save();

        return $this->redirect('travauxmarc_edit', $travaumarc);
    }

    public function executeCreatePapier(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();
        $this->secureEtablissement($etablissement);
        $travauxmarc = TravauxMarcClient::getInstance()->createDoc($etablissement->identifiant, $request->getParameter('campagne', ConfigurationClient::getInstance()->getCampagneManager()->getCurrent(), true));
        $travauxmarc->save();

        return $this->redirect('travauxmarc_edit', $travauxmarc);
    }

    public function executeEdit(sfWebRequest $request) {
        $travaumarc = $this->getRoute()->getTravauxMarc();
        $this->secure(TravauxMarcSecurity::EDITION, $travaumarc);

        if ($travaumarc->exist('etape') && $travaumarc->etape) {
            return $this->redirect('travauxmarc_' . $travaumarc->etape, $travaumarc);
        }

        return $this->redirect('travauxmarc_exploitation', $travaumarc);
    }

    public function executeDelete(sfWebRequest $request) {
        $travaumarc = $this->getRoute()->getTravauxMarc();
        $this->secure(TravauxMarcSecurity::EDITION, $travaumarc);
        $etablissement = $travaumarc->getEtablissementObject();
        $travaumarc->delete();
        $this->getUser()->setFlash("notice", "Le déclaration d'ouverture des travaux de distillation a été supprimé avec succès.");

        return $this->redirect('declaration_etablissement', $etablissement);
    }

    public function executeDevalidation(sfWebRequest $request) {
        $travauxmarc = $this->getRoute()->getTravauxMarc();
        $this->secure(TravauxMarcSecurity::DEVALIDATION, $travauxmarc);

        $travauxmarc->devalidate();
        $travauxmarc->save();

        $this->getUser()->setFlash("notice", "La déclaration a été dévalidé avec succès.");

        return $this->redirect('declaration_etablissement', $travauxmarc->getEtablissementObject());
    }

    public function executeExploitation(sfWebRequest $request) {
        $this->travauxmarc = $this->getRoute()->getTravauxMarc();

        $this->secure(TravauxMarcSecurity::EDITION, $this->travauxmarc);

        $this->travauxmarc->storeEtape($this->getEtape($this->travauxmarc, TravauxMarcEtapes::ETAPE_EXPLOITATION));

        $this->travauxmarc->save();

        $this->etablissement = $this->travauxmarc->getEtablissementObject();

        $this->form = new EtablissementForm($this->etablissement, array("use_email" => !$this->travauxmarc->isPapier()));

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        $this->travauxmarc->storeDeclarant();
        $this->travauxmarc->save();

        if ($this->form->hasUpdatedValues() && !$this->travauxmarc->isPapier()) {
        	Email::getInstance()->sendNotificationModificationsExploitation($this->travauxmarc->getEtablissementObject(), $this->form->getUpdatedValues());
        }

        return $this->redirect('travauxmarc_fournisseurs', $this->travauxmarc);
    }

    public function executeFournisseurs(sfWebRequest $request) {
        $this->travauxmarc = $this->getRoute()->getTravauxMarc();
        $this->secure(TravauxMarcSecurity::EDITION, $this->travauxmarc);

        if($this->travauxmarc->storeEtape($this->getEtape($this->travauxmarc, TravauxMarcEtapes::ETAPE_FOURNISSEURS))) {
            $this->travauxmarc->save();
        }

        $this->form = new TravauxMarcFournisseursForm($this->travauxmarc->fournisseurs);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {
            return sfView::SUCCESS;
        }

        $this->form->save();

        if ($request->isXmlHttpRequest()) {

            return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->travauxmarc->_id, "revision" => $this->travauxmarc->_rev))));
        }

        return $this->redirect('travauxmarc_distillation', $this->travauxmarc);
    }

    public function executeDistillation(sfWebRequest $request) {
        $this->travauxmarc = $this->getRoute()->getTravauxMarc();
        $this->secure(TravauxMarcSecurity::EDITION, $this->travauxmarc);

        if($this->travauxmarc->storeEtape($this->getEtape($this->travauxmarc, TravauxMarcEtapes::ETAPE_DISTILLATION))) {
            $this->travauxmarc->save();
        }

        $this->form = new TravauxMarcDistillationForm($this->travauxmarc);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {
            return sfView::SUCCESS;
        }

        $this->form->save();

        if ($request->isXmlHttpRequest()) {

            return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->travauxmarc->_id, "revision" => $this->travauxmarc->_rev))));
        }

        return $this->redirect('travauxmarc_validation', $this->travauxmarc);
    }

    public function executeValidation(sfWebRequest $request) {
        $this->travauxmarc = $this->getRoute()->getTravauxMarc();

        $this->secure(TravauxMarcSecurity::EDITION, $this->travauxmarc);

        $this->travauxmarc->storeEtape($this->getEtape($this->travauxmarc, DrevEtapes::ETAPE_VALIDATION));
        $this->travauxmarc->save();

        $this->validation = new TravauxMarcValidation($this->travauxmarc);

        $this->form = new TravauxMarcValidationForm($this->travauxmarc);

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

        if($this->travauxmarc->isPapier()) {
            $this->getUser()->setFlash("notice", "La déclaration a bien été validée");

            $this->travauxmarc->validate($this->form->getValue("date"));
            $this->travauxmarc->validateOdg();
            $this->travauxmarc->save();

            return $this->redirect('travauxmarc_visualisation', $this->travauxmarc);
        }

        $this->travauxmarc->validate();
        $this->travauxmarc->save();

        $this->sendDRevMarcValidation($this->travauxmarc);

        return $this->redirect('travauxmarc_confirmation', $this->travauxmarc);
    }

    public function executeValidationAdmin(sfWebRequest $request) {
        $this->travauxmarc = $this->getRoute()->getTravauxMarc();

        $this->secure(DRevSecurity::VALIDATION_ADMIN, $this->travauxmarc);

        $this->travauxmarc->validation_odg = date('Y-m-d');
        $this->travauxmarc->save();

        $this->sendDRevMarcConfirmee($this->travauxmarc);

        $this->getUser()->setFlash("notice", "La déclaration a bien été approuvée. Un email a été envoyé au télédéclarant.");

        return $this->redirect('travauxmarc_visualisation', $this->travauxmarc);
    }

    public function executeConfirmation(sfWebRequest $request) {
        $this->travauxmarc = $this->getRoute()->getTravauxMarc();
        $this->secure(TravauxMarcSecurity::VISUALISATION, $this->travauxmarc);
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->travauxmarc = $this->getRoute()->getTravauxMarc();
        $this->secure(TravauxMarcSecurity::VISUALISATION, $this->travauxmarc);
    }

    public function executePDF(sfWebRequest $request) {
        $travaumarc = $this->getRoute()->getTravauxMarc();
        $this->secure(TravauxMarcSecurity::VISUALISATION, $travaumarc);
        $this->document = new ExportTravauxMarcPdf($travaumarc, $this->getRequestParameter('output', 'pdf'), false);
        $this->document->setPartialFunction(array($this, 'getPartial'));

        if ($request->getParameter('force')) {
            $this->document->removeCache();
        }

        $this->document->generate();

        $this->document->addHeaders($this->getResponse());

        return $this->renderText($this->document->output());
    }

    protected function getEtape($travaumarc, $etape) {
        $travauxmarcEtapes = TravauxMarcEtapes::getInstance();
        if (!$travaumarc->exist('etape')) {
            return $etape;
        }
        return ($travauxmarcEtapes->isLt($travaumarc->etape, $etape)) ? $etape : $travaumarc->etape;
    }

    protected function sendDRevMarcValidation($travaumarc) {
        $pdf = new ExportDRevMarcPdf($travaumarc, 'pdf', true);
        $pdf->setPartialFunction(array($this, 'getPartial'));
        $pdf->removeCache();
        $pdf->generate();
        Email::getInstance()->sendDRevMarcValidation($travaumarc);
    }

    protected function sendDrevMarcConfirmee($travaumarc) {
        Email::getInstance()->sendDrevMarcConfirmee($travaumarc);
    }

    protected function secureEtablissement($etablissement) {
        if (!EtablissementSecurity::getInstance($this->getUser(), $etablissement)->isAuthorized(array())) {

            return $this->forwardSecure();
        }
    }

    protected function secure($droits, $doc) {
        if (!TravauxMarcSecurity::getInstance($this->getUser(), $doc)->isAuthorized($droits)) {

            return $this->forwardSecure();
        }
    }

    protected function forwardSecure() {
        $this->context->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

        throw new sfStopException();
    }

}

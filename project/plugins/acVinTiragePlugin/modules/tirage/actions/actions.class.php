<?php

class tirageActions extends sfActions {

    public function executeCreate(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();
        $this->secureEtablissement($etablissement);
        $campagne = $request->getParameter('campagne', ConfigurationClient::getInstance()->getCampagneManager()->getCurrent());
        $tirage = TirageClient::getInstance()->createDoc($etablissement->identifiant, $campagne);
        $nbDeclaration = TirageClient::getInstance()->getLastNumero($etablissement->identifiant, $campagne);
        $tirage->save();
        $tirage->storeDRFromDRev();

        if ($nbDeclaration >= 1) {
            $idLast = 'TIRAGE-' . $etablissement->identifiant . '-' . $campagne . sprintf('%02d', $nbDeclaration);
            $lastTirage = TirageClient::getInstance()->find($idLast);
            if ($lastTirage && $lastTirage->exist('documents') && $lastTirage->exist('_attachments')) {
                $tirage->add('documents', $lastTirage->documents);
                if (file_get_contents($lastTirage->getAttachmentUri("DR.pdf"))) {
                    $tirage->storeAsAttachment(file_get_contents($lastTirage->getAttachmentUri("DR.pdf")), "DR.pdf", "application/pdf");
                }
            }
        }
        $tirage->save();
        return $this->redirect('tirage_edit', $tirage);
    }

    public function executeCreatePapier(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->secureEtablissement($this->etablissement);

        $this->form = new TirageCreationForm(array("campagne" => $request->getParameter('campagne', ConfigurationClient::getInstance()->getCampagneManager()->getCurrent())));

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $tirage = TirageClient::getInstance()->createDoc($this->etablissement->identifiant, $this->form->getValue('campagne'), true);
        $tirage->save();

        return $this->redirect('tirage_edit', $tirage);
    }

    public function executeEdit(sfWebRequest $request) {
        $tirage = $this->getRoute()->getTirage();
        $this->secure(TirageSecurity::EDITION, $tirage);

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

        return $this->redirect('declaration_etablissement', $tirage->getEtablissementObject());
    }

    public function executeDelete(sfWebRequest $request) {
        $tirage = $this->getRoute()->getTirage();
        $etablissement = $tirage->getEtablissementObject();
        $this->secure(TirageSecurity::EDITION, $tirage);

        $tirage->delete();
        $this->getUser()->setFlash("notice", 'La déclaration de tirage a été supprimé avec succès.');

        return $this->redirect('declaration_etablissement', $etablissement);
    }

    public function executeExploitation(sfWebRequest $request) {
        $this->tirage = $this->getRoute()->getTirage();

        $this->secure(TirageSecurity::EDITION, $this->tirage);

        $this->tirage->storeEtape($this->getEtape($this->tirage, TirageEtapes::ETAPE_EXPLOITATION));

        $this->tirage->save();

        $this->etablissement = $this->tirage->getEtablissementObject();

        $this->form = new TirageExploitationForm($this->tirage, array("use_email" => !$this->tirage->isPapier()));

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

        if ($this->form->hasUpdatedValues() && !$this->tirage->isPapier()) {
        	Email::getInstance()->sendNotificationModificationsExploitation($this->tirage->getEtablissementObject(), $this->form->getUpdatedValues());
        }

        if ($request->isXmlHttpRequest()) {

            return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->etablissement->_id, "revision" => $this->etablissement->_rev))));
        }

        if ($this->form->getValue('lieu_exploitation')) {
            $this->tirage->lieu_exploitation = $this->form->getValue('lieu_exploitation');
        }

        return $this->redirect('tirage_vin', $this->tirage);
    }

    public function executeVin(sfWebRequest $request) {
        $this->tirage = $this->getRoute()->getTirage();
        $this->secure(TirageSecurity::EDITION, $this->tirage);
        $this->form = new TirageVinForm($this->tirage);
        if (!$request->isMethod(sfWebRequest::POST)) {
            return sfView::SUCCESS;
        }


        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {
            return sfView::SUCCESS;
        }

        $this->form->save();
        if ($request->isXmlHttpRequest()) {
            return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->tirage->_id, "revision" => $this->tirage->_rev))));
        }
        return $this->redirect('tirage_lots', $this->tirage);
    }

    public function executeLots(sfWebRequest $request) {
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

        if ($request->isXmlHttpRequest()) {
            return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->tirage->_id, "revision" => $this->tirage->_rev))));
        }

        return $this->redirect('tirage_validation', $this->tirage);
    }

    public function executeDrRecuperation(sfWebRequest $request) {
        $tirage = $this->getRoute()->getTirage();
        $this->secure(TirageSecurity::EDITION, $tirage);

        return $this->redirect(sfConfig::get('app_url_dr_recuperation') .
                        "?" .
                        http_build_query(array(
                            'url' => $this->generateUrl('tirage_dr_import', $tirage, true),
                            'id' => sprintf('DR-%s-%s', $tirage->identifiant, $tirage->getCampagneDR()))));
    }

    public function executeDrImport(sfWebRequest $request) {
        $this->tirage = $this->getRoute()->getTirage();
        $this->secure(TirageSecurity::EDITION, $this->tirage);

        if (!$request->getParameter('pdf')) {

            $this->getUser()->setFlash('error', "La récupération de la DR a échoué");

            return $this->redirect($this->generateUrl('tirage_validation', array("sf_subject" => $this->tirage)));
        }

        $this->tirage->storeAsAttachment(base64_decode($request->getParameter('pdf')), "DR.pdf", "application/pdf");

        $this->tirage->save();

        $this->getUser()->setFlash('success', "La DR a bien été récupérée depuis le CIVA");

        return $this->redirect($this->generateUrl('tirage_validation', $this->tirage));
    }

    public function executeValidation(sfWebRequest $request) {
        $this->tirage = $this->getRoute()->getTirage();

        $this->secure(TirageSecurity::EDITION, $this->tirage);

        $this->tirage->storeEtape($this->getEtape($this->tirage, TirageEtapes::ETAPE_VALIDATION));
        $this->tirage->save();

        $this->tirage->cleanDoc();

        $this->validation = new TirageValidation($this->tirage);

        $this->form = new TirageValidationForm($this->tirage, array(), array('engagements' => $this->validation->getPoints(TirageValidation::TYPE_ENGAGEMENT)));

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        if (!$this->validation->isValide() && !$this->getUser()->isAdmin()) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $documents = $this->tirage->getOrAdd('documents');

        foreach ($this->validation->getPoints(TirageValidation::TYPE_ENGAGEMENT) as $engagement) {
            $document = $documents->add($engagement->getCode());
            $document->statut = ($engagement->getCode() == TirageDocuments::DOC_PRODUCTEUR && $this->tirage->hasDr()) ? TirageDocuments::STATUT_RECU : TirageDocuments::STATUT_EN_ATTENTE;
        }

        if ($this->tirage->isPapier()) {
            $this->getUser()->setFlash("notice", "La déclaration a bien été validée");

            $this->tirage->validate($this->form->getValue("date"));
            if ($this->tirage->hasCompleteDocuments()) {
                $this->tirage->validateOdg();
            }
            $this->tirage->save();

            return $this->redirect('tirage_visualisation', $this->tirage);
        }

        $this->tirage->validate();
        $this->tirage->save();

        $this->sendTirageValidation($this->tirage);

        return $this->redirect('tirage_confirmation', $this->tirage);
    }

    public function executeValidationAdmin(sfWebRequest $request) {
        $this->tirage = $this->getRoute()->getTirage();
        $this->secure(TirageSecurity::VALIDATION_ADMIN, $this->tirage);

        $this->tirage->validateOdg();
        $this->tirage->save();

        if ($this->tirage->isPapier()) {
            $this->getUser()->setFlash("notice", "La déclaration a bien été approuvée.");

            return $this->redirect('tirage_visualisation', array('sf_subject' => $this->tirage, 'service' => isset($service) ? $service : null));
        }

        $this->sendTirageConfirmee($this->tirage);

        $this->getUser()->setFlash("notice", "La déclaration a bien été approuvée. Un email a été envoyé au télédéclarant.");

        $service = $request->getParameter("service");

        return $this->redirect('tirage_visualisation', array('sf_subject' => $this->tirage, 'service' => isset($service) ? $service : null));
    }

    public function executeConfirmation(sfWebRequest $request) {
        $this->tirage = $this->getRoute()->getTirage();
        $this->secure(TirageSecurity::VISUALISATION, $this->tirage);
        $this->etablissement = $this->tirage->getEtablissementObject();
        $this->nbDeclaration = TirageClient::getInstance()->getLastNumero($this->tirage->identifiant, $this->tirage->campagne);
        $nextNumero = $this->nbDeclaration + 1;
        $this->nieme = '';
        if ($nextNumero > 1) {
            $this->nieme = $nextNumero . "ème";
        }
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->tirage = $this->getRoute()->getTirage();
        $this->secure(TirageSecurity::VISUALISATION, $this->tirage);

        $this->service = $request->getParameter('service');

        $documents = $this->tirage->getOrAdd('documents');

        if ($this->getUser()->isAdmin() && $this->tirage->validation && !$this->tirage->validation_odg) {
            $this->validation = new TirageValidation($this->tirage);
        }

        $this->form = (count($documents->toArray()) && $this->getUser()->isAdmin() && $this->tirage->validation && !$this->tirage->validation_odg && !$this->tirage->hasCompleteDocuments()) ? new TirageDocumentsForm($documents) : null;

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }
        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect('tirage_visualisation', $this->tirage);
    }

    public function executePDF(sfWebRequest $request) {
        $tirage = $this->getRoute()->getTirage();
        $this->secure(TirageSecurity::VISUALISATION, $tirage);

        if (!$tirage->validation) {
            $tirage->cleanDoc();
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

    protected function secureEtablissement($etablissement) {
        if (!EtablissementSecurity::getInstance($this->getUser(), $etablissement)->isAuthorized(array())) {
            return $this->forwardSecure();
        }
    }

    protected function forwardSecure() {
        $this->context->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));
        throw new sfStopException();
    }

    protected function sendTirageValidation($tirage) {
        if ($tirage->isPapier()) {

            return false;
        }

        $pdf = new ExportTiragePdf($tirage, 'pdf', true);
        $pdf->setPartialFunction(array($this, 'getPartial'));
        $pdf->removeCache();
        $pdf->generate();
        Email::getInstance()->sendTirageValidation($tirage);
    }

    protected function sendTirageConfirmee($tirage) {
        if ($tirage->isPapier()) {

            return false;
        }

        Email::getInstance()->sendTirageConfirmee($tirage);
    }

    public function executeDrPdf(sfWebRequest $request) {
        $tirage = $this->getRoute()->getTirage();
        $this->secure(TirageSecurity::VISUALISATION, $tirage);

        $file = file_get_contents($tirage->getAttachmentUri('DR.pdf'));

        if (!$file) {

            $this->forward404();
        }

        $this->getResponse()->setHttpHeader('Content-Type', 'application/pdf');
        $this->getResponse()->setHttpHeader('Content-disposition', sprintf('attachment; filename="DR-%s-%s.pdf"', $tirage->identifiant, $tirage->getCampagneDR()));
        $this->getResponse()->setHttpHeader('Content-Transfer-Encoding', 'binary');
        $this->getResponse()->setHttpHeader('Pragma', '');
        $this->getResponse()->setHttpHeader('Cache-Control', 'public');
        $this->getResponse()->setHttpHeader('Expires', '0');

        return $this->renderText($file);
    }

}

<?php

class drevmarcActions extends sfActions {

    public function executeCreate(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();
        $this->secureEtablissement($etablissement);
        $drevmarc = DRevMarcClient::getInstance()->createDoc($etablissement->identifiant, ConfigurationClient::getInstance()->getCampagneManager()->getCurrent());
        $drevmarc->save();

        return $this->redirect('drevmarc_edit', $drevmarc);
    }

    public function executeCreatePapier(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();
        $this->secureEtablissement($etablissement);
        $drev = DRevMarcClient::getInstance()->createDoc($etablissement->identifiant, ConfigurationClient::getInstance()->getCampagneManager()->getCurrent(), true);
        $drev->save();

        return $this->redirect('drevmarc_edit', $drev);
    }

    public function executeEdit(sfWebRequest $request) {
        $drevmarc = $this->getRoute()->getDRevMarc();
        $this->secure(DRevMarcSecurity::EDITION, $drevmarc);

        if ($drevmarc->exist('etape') && $drevmarc->etape) {
            return $this->redirect('drevmarc_' . $drevmarc->etape, $drevmarc);
        }

        return $this->redirect('drevmarc_exploitation', $drevmarc);
    }

    public function executeDelete(sfWebRequest $request) {
        $drevmarc = $this->getRoute()->getDRevMarc();
        $this->secure(DRevMarcSecurity::EDITION, $drevmarc);
        $etablissement = $drevmarc->getEtablissementObject();
        $drevmarc->delete();
        $this->getUser()->setFlash("notice", 'La DRev a été supprimé avec succès.');

        return $this->redirect('declaration_etablissement', $etablissement);
    }

    public function executeDevalidation(sfWebRequest $request) {
        $drev = $this->getRoute()->getDRevMarc();

        $this->secure(DRevMarcSecurity::DEVALIDATION, $drev);

        $drev->validation = null;
        $drev->validation_odg = null;
        $drev->save();

        $this->getUser()->setFlash("notice", "La déclaration a été dévalidé avec succès.");

        return $this->redirect('declaration_etablissement', $drev->getEtablissementObject());
    }

    public function executeExploitation(sfWebRequest $request) {
        $this->drevmarc = $this->getRoute()->getDRevMarc();

        $this->secure(DRevMarcSecurity::EDITION, $this->drevmarc);

        $this->drevmarc->storeEtape($this->getEtape($this->drevmarc, DrevMarcEtapes::ETAPE_EXPLOITATION));

        $this->drevmarc->save();

        $this->etablissement = $this->drevmarc->getEtablissementObject();

        $this->form = new EtablissementForm($this->etablissement, array("use_email" => !$this->drevmarc->isPapier()));

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        $this->drevmarc->storeDeclarant();
        $this->drevmarc->save();

        if ($this->form->hasUpdatedValues() && !$this->drevmarc->isPapier()) {
        	Email::getInstance()->sendNotificationModificationsExploitation($this->drevmarc->getEtablissementObject(), $this->form->getUpdatedValues());
        }

        return $this->redirect('drevmarc_revendication', $this->drevmarc);
    }

    public function executeRevendication(sfWebRequest $request) {
        $this->drevmarc = $this->getRoute()->getDRevMarc();

        $this->secure(DRevMarcSecurity::EDITION, $this->drevmarc);

        $this->drevmarc->storeEtape($this->getEtape($this->drevmarc, DrevMarcEtapes::ETAPE_REVENDICATION));
        $this->drevmarc->save();

        $this->form = new DRevMarcRevendicationForm($this->drevmarc);
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->form->save();
                if ($request->isXmlHttpRequest()) {

                    return $this->renderText(json_encode(array("success" => true, "document" => array("id" => $this->drevmarc->_id, "revision" => $this->drevmarc->_rev))));
                }
                return $this->redirect('drevmarc_validation', $this->drevmarc);
            }
        }
    }

    public function executeValidation(sfWebRequest $request) {
        $this->drevmarc = $this->getRoute()->getDRevMarc();

        $this->secure(DRevMarcSecurity::EDITION, $this->drevmarc);

        $this->drevmarc->storeEtape($this->getEtape($this->drevmarc, DrevEtapes::ETAPE_VALIDATION));
        $this->drevmarc->save();

        $this->validation = new DRevMarcValidation($this->drevmarc);

        $this->form = new DRevMarcValidationForm($this->drevmarc);

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

        if($this->drevmarc->isPapier()) {
            $this->getUser()->setFlash("notice", "La déclaration a bien été validée");

            $this->drevmarc->validate($this->form->getValue("date"));
            $this->drevmarc->validateOdg();
            $this->drevmarc->save();

            return $this->redirect('drevmarc_visualisation', $this->drevmarc);
        }

        $this->drevmarc->validate();
        $this->drevmarc->save();

        $this->sendDRevMarcValidation($this->drevmarc);

        return $this->redirect('drevmarc_confirmation', $this->drevmarc);
    }

    public function executeValidationAdmin(sfWebRequest $request) {
        $this->drevmarc = $this->getRoute()->getDRevMarc();

        $this->secure(DRevSecurity::VALIDATION_ADMIN, $this->drevmarc);

        $this->drevmarc->validation_odg = date('Y-m-d');
        $this->drevmarc->save();

        $this->sendDRevMarcConfirmee($this->drevmarc);

        $this->getUser()->setFlash("notice", "La déclaration a bien été approuvée. Un email a été envoyé au télédéclarant.");

        return $this->redirect('drevmarc_visualisation', $this->drevmarc);
    }

    public function executeConfirmation(sfWebRequest $request) {
        $this->secure(DRevMarcSecurity::VISUALISATION, $drevmarc);
        $this->drevmarc = $this->getRoute()->getDRevMarc();
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->drevmarc = $this->getRoute()->getDRevMarc();
        $this->secure(DRevMarcSecurity::VISUALISATION, $this->drevmarc);
    }

    public function executePDF(sfWebRequest $request) {
        $drevmarc = $this->getRoute()->getDRevMarc();
        $this->secure(DRevMarcSecurity::VISUALISATION, $drevmarc);
        $this->document = new ExportDRevMarcPdf($drevmarc, $this->getRequestParameter('output', 'pdf'), false);
        $this->document->setPartialFunction(array($this, 'getPartial'));

        if ($request->getParameter('force')) {
            $this->document->removeCache();
        }

        $this->document->generate();

        $this->document->addHeaders($this->getResponse());

        return $this->renderText($this->document->output());
    }

    protected function getEtape($drevmarc, $etape) {
        $drevEtapes = DrevMarcEtapes::getInstance();
        if (!$drevmarc->exist('etape')) {
            return $etape;
        }
        return ($drevEtapes->isLt($drevmarc->etape, $etape)) ? $etape : $drevmarc->etape;
    }

    protected function sendDRevMarcValidation($drevmarc) {
        $pdf = new ExportDRevMarcPdf($drevmarc, 'pdf', true);
        $pdf->setPartialFunction(array($this, 'getPartial'));
        $pdf->removeCache();
        $pdf->generate();
        Email::getInstance()->sendDRevMarcValidation($drevmarc);
    }

    protected function sendDrevMarcConfirmee($drevmarc) {
        Email::getInstance()->sendDrevMarcConfirmee($drevmarc);
    }

    protected function secureEtablissement($etablissement) {
        if (!EtablissementSecurity::getInstance($this->getUser(), $etablissement)->isAuthorized(array())) {

            return $this->forwardSecure();
        }
    }

    protected function secure($droits, $doc) {
        if (!DRevMarcSecurity::getInstance($this->getUser(), $doc)->isAuthorized($droits)) {

            return $this->forwardSecure();
        }
    }

    protected function forwardSecure() {
        $this->context->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

        throw new sfStopException();
    }

}

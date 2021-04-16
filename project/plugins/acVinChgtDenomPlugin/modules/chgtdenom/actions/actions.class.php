<?php

class chgtdenomActions extends sfActions
{
    public function executeCreateLot(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();
        $lot = $request->getParameter('lot');
        $this->secureEtablissement(null, $etablissement);

        $papier = ($this->getUser()->isAdmin()) ? 1 : 0;

        $chgtDenom = ChgtDenomClient::getInstance()->createDoc($etablissement->identifiant, null, $papier);

        // Format de $lot : DEGUSTATION-20210101:2020-2021-00001-00001 | document_id:unique_id
        $chgtDenom->changement_origine_id_document = strtok($lot, ':');
        $chgtDenom->changement_origine_lot_unique_id = strtok(':');
        $chgtDenom->save();

        return $this->redirect('chgtdenom_edition', array('id' => $chgtDenom->_id));
    }

    public function executeLots(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->campagne = $request->getParameter('campagne');
        $this->lots = ChgtDenomClient::getInstance()->getLotsChangeable($this->etablissement->identifiant);
    }

    public function executeEdition(sfWebRequest $request) {
        $this->chgtDenom = $this->getRoute()->getChgtDenom();
        $this->secureIsValide($this->chgtDenom);

        if(!$this->chgtDenom->getLotOrigine()) {
            return $this->redirect('chgtdenom_lots', array('sf_subject' => $this->chgtDenom->getEtablissementObject(), 'campagne' => $this->chgtDenom->campagne));
        }

        $this->form = new ChgtDenomForm($this->chgtDenom);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect('chgtdenom_validation', $this->chgtDenom);
    }

    public function executeLogement(sfWebRequest $request) {
        $chgtDenom = $this->getRoute()->getChgtDenom();
        $this->secureIsValide($chgtDenom);
        $key = $request->getParameter("key", null);

        if (!$key) {
          $this->getUser()->setFlash("erreur", 'Une erreur est survenue.');
          return $this->redirect('chgtdenom_validation', $chgtDenom);
        }

        $key = str_replace('ind', '', $key);

        if (!$chgtDenom->lots->exist($key)) {
          $this->getUser()->setFlash("erreur", 'Une erreur est survenue.');
          return $this->redirect('chgtdenom_validation', $chgtDenom);
        }

        $form = new ChgtDenomLogementForm($chgtDenom->lots->get($key));

        $form->bind($request->getParameter($form->getName()));

        if (!$form->isValid()) {
            $this->getUser()->setFlash("erreur", 'Une erreur est survenue.');
            return $this->redirect('chgtdenom_validation', $chgtDenom);
        }

        $form->save();
        $this->getUser()->setFlash("notice", 'Le logement a été modifié avec succès.');
        return $this->redirect('chgtdenom_validation', $chgtDenom);
    }

    public function executeValidation(sfWebRequest $request) {
        $this->chgtDenom = $this->getRoute()->getChgtDenom();
        $this->secureIsValide($this->chgtDenom);
        $this->isAdmin = $this->getUser()->isAdmin();

        $this->validation = new ChgtDenomValidation($this->chgtDenom);

        $this->form = new ChgtDenomValidationForm($this->chgtDenom, array(), array('isAdmin' => $this->isAdmin));

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        if($this->isAdmin) {
            $this->chgtDenom->validateOdg();
            $this->chgtDenom->save();
            $this->getUser()->setFlash("notice", "Le changement dénommination a été validé et approuvé");

            return $this->redirect('chgtdenom_visualisation', $this->chgtDenom);
        }

        return $this->redirect('chgtdenom_visualisation', $this->chgtDenom);
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->chgtDenom = $this->getRoute()->getChgtDenom();
        $this->isAdmin = $this->getUser()->isAdmin();

        $this->form = null;
        if ($this->isAdmin && !$this->chgtDenom->isApprouve()) {
          $this->form = new ChgtDenomValidationForm($this->chgtDenom, array(), array('isAdmin' => $this->isAdmin));
        }

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        if($this->isAdmin) {
            $this->chgtDenom->validateOdg();
            $this->chgtDenom->save();
            $this->getUser()->setFlash("notice", "Le changement dénommination a été approuvé");
        }

        return $this->redirect('chgtdenom_visualisation', $this->chgtDenom);
    }

    public function executeDevalidation(sfWebRequest $request) {
        $chgtDenom = $this->getRoute()->getChgtDenom();
        if (!$this->getUser()->isAdmin()) {
          $this->secure(ChgtDenomSecurity::DEVALIDATION , $chgtDenom);
        }

        if($chgtDenom->hasLotsUtilises()) {
            throw new Exception("Dévalidation impossible car des lots dans cette déclaration sont utilisés");
        }

        $chgtDenom->devalidate();
        $chgtDenom->save();

        $this->getUser()->setFlash("notice", "La déclaration a été dévalidé avec succès.");

        return $this->redirect($this->generateUrl('chgtdenom_edition', $chgtDenom));
    }

    public function executeSuppression(sfWebRequest $request) {
        $this->chgtDenom = $this->getRoute()->getChgtDenom();
        $this->secureIsValide($this->chgtDenom);
        $identifiant = $this->chgtDenom->identifiant;
        $this->chgtDenom->delete();
        return $this->redirect('declaration_etablissement', array('identifiant' => $identifiant));
    }

    protected function secureEtablissement($droits, $etablissement) {
        if (!EtablissementSecurity::getInstance($this->getUser(), $etablissement)->isAuthorized($droits)) {

            return $this->forwardSecure();
        }
    }

    public function executeChgtDenomPDF(sfWebRequest $request)
    {
        $chgtDenom = $this->getRoute()->getChgtDenom();
        $this->secureEtablissement(null, $chgtDenom->getEtablissementObject());

        $this->document = new ExportChgtDenomPDF($chgtDenom, $request->getParameter('output', 'pdf'), false);
        $this->document->setPartialFunction(array($this, 'getPartial'));
        if ($request->getParameter('force')) {
            $this->document->removeCache();
        }
        $this->document->generate();
        $this->document->addHeaders($this->getResponse());
        return $this->renderText($this->document->output());
    }

    protected function secureIsValide($chgtDenom) {
      if ($chgtDenom->isValide()) {
        return $this->redirect('chgtdenom_visualisation', $chgtDenom);
      }
    }

    protected function forwardSecure() {
        $this->context->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

        throw new sfStopException();
    }

}

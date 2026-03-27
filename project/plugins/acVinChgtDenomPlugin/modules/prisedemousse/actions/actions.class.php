<?php

class prisedemousseActions extends sfActions
{
    public function executeCreateFromLot(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();
        $lot = $request->getParameter('lot');
        $this->secureEtablissement(null, $etablissement);

        $papier = ($this->getUser()->isAdmin()) ? 1 : 0;
        $docid = strtok($lot, ':');
        $unique_id = strtok(':');
        $doc = acCouchdbManager::getClient()->find($docid);
        $this->forward404Unless($doc);
        $lot = $doc->getLot($unique_id);
        $this->forward404Unless($lot);

        $prisedemousse = PriseDeMousseClient::getInstance()->createDoc($etablissement->identifiant, $lot, null, $papier);
        $prisedemousse->save();

        return $this->redirect('prisedemousse_edition', array('id' => $prisedemousse->_id));
    }

    public function executeLots(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->lots = PriseDeMousseClient::getInstance()->getLotsAvailable($this->etablissement->identifiant);

    }

    public function executeEdition(sfWebRequest $request) {
        $this->prisedemousse = $this->getRoute()->getPriseDeMousse();
        $this->secureIsValide($this->prisedemousse);

        if($this->prisedemousse->getLotOrigine() === null) {
            return $this->redirect('prisedemousse_lots', array('sf_subject' => $this->chgtDenom->getEtablissementObject()));
        }

        $this->form = new PriseDeMousseForm($this->prisedemousse);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect('prisedemousse_validation', $this->prisedemousse);
    }

    public function executeLogement(sfWebRequest $request) {
        $chgtDenom = $this->getRoute()->getPriseDeMousse();
        $this->secureIsValide($chgtDenom);

        if ($chgtDenom->isValide()) {
            $this->getUser()->setFlash("error", 'Le changement est validé');
            return $this->redirect('prisedemousse_validation', $chgtDenom);
        }
        $form = new ChgtDenomLogementForm($chgtDenom);

        $form->bind($request->getParameter($form->getName()));

        if (!$form->isValid()) {
            $this->getUser()->setFlash("error", 'Une erreur est survenue : '.strip_tags($form->renderGlobalErrors()));
            return $this->redirect('prisedemousse_validation', $chgtDenom);
        }

        $form->save();
        $this->getUser()->setFlash("notice", 'Le logement a été modifié avec succès.');
        return $this->redirect('prisedemousse_validation', $chgtDenom);
    }

    public function executeValidation(sfWebRequest $request) {
        $this->prisedemousse = $this->getRoute()->getPriseDeMousse();

        if ($this->prisedemousse->isValide()) {
            return $this->redirect('prisedemousse_visualisation', $this->prisedemousse);
        }
        $this->prisedemousse->generateLots();
        $this->secureIsValide($this->prisedemousse);
        $this->isAdmin = $this->getUser()->isAdmin();

        if (! $this->prisedemousse->isValide()) {
            $this->formLogement = new ChgtDenomLogementForm($this->prisedemousse);
        }

        $this->validation = new PriseDeMousseValidation($this->prisedemousse);

        $this->form = new ChgtDenomValidationForm($this->prisedemousse, array(), array('isAdmin' => $this->isAdmin, 'withDate' => $this->isAdmin, 'engagements' => $this->validation->getEngagements()));

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        if($this->isAdmin) {
            $this->prisedemousse->validateOdg(null, $request->getParameter('region', $this->getUser()->getRegion()));
            $this->prisedemousse->save();
            $this->getUser()->setFlash("notice", "La prise de mousse a été validée et approuvée");

            return $this->redirect('prisedemousse_visualisation', $this->prisedemousse);
        }

        return $this->redirect('prisedemousse_visualisation', $this->chgtDenom);
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->prisedemousse = $this->getRoute()->getPriseDeMousse();
        $this->isAdmin = $this->getUser()->isAdmin();

        if (!$this->prisedemousse->isValide()) {
            return $this->redirect('prisedemousse_validation', $this->prisedemousse);
        }

        $this->form = null;
        if ($this->isAdmin && !$this->prisedemousse->isApprouve()) {
          $this->validation = new PriseDeMousseValidation($this->prisedemousse);
          $this->form = new ChgtDenomValidationForm($this->prisedemousse, array(), array('isAdmin' => $this->isAdmin, 'engagements' => $this->validation->getEngagements()));
        }

        if (!$request->isMethod(sfWebRequest::POST)) {
            if (!$this->prisedemousse->isApprouve()) {
                $this->prisedemousse->generateLots();
            }
            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        if($this->isAdmin) {
            $this->prisedemousse->validateOdg();
            $this->prisedemousse->save();
            $this->getUser()->setFlash("notice", "La prise de mousse a été approuvée");
        }

        return $this->redirect('prisedemousse_visualisation', $this->chgtDenom);
    }

    public function executeDevalidation(sfWebRequest $request) {
        $chgtDenom = $this->getRoute()->getPriseDeMousse();
        if (!$this->getUser()->isAdmin()) {
          $this->secure(ChgtDenomSecurity::DEVALIDATION , $chgtDenom);
        }

        if($chgtDenom->hasLotsUtilises()) {
            throw new Exception("Dévalidation impossible car des lots dans cette déclaration sont utilisés");
        }
        try {
            $chgtDenom->devalidate();
        }catch(sfException $e) {
            $this->getUser()->setFlash("error", $e->getMessage());
            return $this->redirect($this->generateUrl('prisedemousse_visualisation', $chgtDenom));
        }
        $chgtDenom->save();

        $this->getUser()->setFlash("notice", "La déclaration a été dévalidé avec succès.");

        return $this->redirect($this->generateUrl('prisedemousse_edition', $chgtDenom));
    }

    public function executeSuppression(sfWebRequest $request) {
        $this->chgtDenom = $this->getRoute()->getPriseDeMousse();
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
        $chgtDenom = $this->getRoute()->getPriseDeMousse(['allow_habilitation' => true, 'allow_stalker' => true]);
        if (!$chgtDenom->isApprouve()) {
            $chgtDenom->generateLots();
        }
        if (!$this->getUser()->isStalker()) {
            $this->secureEtablissement('habilitation', $chgtDenom->getEtablissementObject());
        }

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
        return $this->redirect('prisedemousse_visualisation', $chgtDenom);
      }
    }

    protected function forwardSecure() {
        $this->context->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

        throw new sfStopException();
    }

}

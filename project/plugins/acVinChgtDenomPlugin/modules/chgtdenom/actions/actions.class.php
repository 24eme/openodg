<?php

class chgtdenomActions extends sfActions {


    public function executeCreate(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();
        $this->secureEtablissement(EtablissementSecurity::DECLARANT_DREV, $etablissement);

        $chgtDenom = ChgtDenomClient::getInstance()->createDoc($etablissement->identifiant);
        $chgtDenom->save();

        return $this->redirect('chgtdenom_lots', $chgtDenom);
    }

    public function executeCreatePapier(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();
        $this->secureEtablissement(EtablissementSecurity::DECLARANT_DREV, $etablissement);

        $chgtDenom = ChgtDenomClient::getInstance()->createDoc($etablissement->identifiant, null, true);
        $chgtDenom->save();

        return $this->redirect('chgtdenom_lots', $chgtDenom);
    }

    public function executeLots(sfWebRequest $request) {
        $this->chgtDenom = $this->getRoute()->getChgtDenom();
        $this->lots = $this->chgtDenom->getMvtLots();
    }

    public function executeEdition(sfWebRequest $request) {
        $this->chgtDenom = $this->getRoute()->getChgtDenom();
        $this->key = $request->getParameter("key", null);
        $firstEdition = true;

        if (!$this->key) {
          $this->key = $this->chgtDenom->getLotKey();
          $firstEdition = false;
        }

        if (!$this->key) {
          return $this->redirect('chgtdenom_lots', $this->chgtDenom);
        }
        $this->chgtDenom->changement_origine_mvtkey = $this->key;

        $this->form = new ChgtDenomForm($this->chgtDenom, $firstEdition);

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

    public function executeValidation(sfWebRequest $request) {
        $this->chgtDenom = $this->getRoute()->getChgtDenom();

        $this->form = new ChgtDenomValidationForm($this->chgtDenom);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect('chgtdenom_visualisation', $this->chgtDenom);
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->chgtDenom = $this->getRoute()->getChgtDenom();

        if ($this->getUser()->isAdmin() && !$this->chgtDenom->isApprouve()) {
          $this->form = new ChgtDenomApprobationForm($this->chgtDenom);
        }

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect('chgtdenom_visualisation', $this->chgtDenom);
    }

    public function executeSuppression(sfWebRequest $request) {
        $this->chgtDenom = $this->getRoute()->getChgtDenom();
        $identifiant = $this->chgtDenom->identifiant;
        $this->chgtDenom->delete();
        return $this->redirect('declaration_etablissement', array('identifiant' => $identifiant));
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

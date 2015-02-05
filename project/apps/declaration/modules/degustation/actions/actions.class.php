<?php

class degustationActions extends sfActions {

    public function executeIndex(sfWebRequest $request) {
        $this->degustation = new Degustation();
        $this->form = new DegustationCreationForm($this->degustation);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect('degustation_creation', $this->degustation);
    }

    public function executeCreation(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();

        $this->form = new DegustationCreationFinForm($this->degustation);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect('degustation_operateurs', $this->degustation);
    }

    public function executeOperateurs(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
    }

    public function executeDegustation(sfWebRequest $request) {
 
    }

    public function executeDegustateurs(sfWebRequest $request) {

    }

    public function executeAgents(sfWebRequest $request) {

    }

    public function executePrelevements(sfWebRequest $request) {

    }

    public function executeValidation(sfWebRequest $request) {
 
    }

    public function executeTournee(sfWebRequest $request) {

    }

    public function executeAffectation(sfWebRequest $request) {

    }
}

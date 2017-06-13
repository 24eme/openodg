<?php

class declarationActions extends sfActions {

    public function executeIndex(sfWebRequest $request) {

        $this->getUser()->signOutEtablissement();

        $this->form = new LoginForm();

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->getUser()->signInEtablissement($this->form->getValue('etablissement'));

        return $this->redirect('declaration_etablissement', $this->form->getValue('etablissement'));
    }

    public function executeEtablissement(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
    }

}

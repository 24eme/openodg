<?php

class compte_teledeclarantActions extends sfActions {

    public function executePremiereConnexion(sfWebRequest $request) {
        $this->etablissement = $this->getUser()->getEtablissement();

        $this->form = new EtablissementConfirmationEmailForm($this->etablissement);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        $this->redirect('declaration_etablissement', $this->etablissement);
    }

    public function executeCreation(sfWebRequest $request) {

    }

    public function executeCreationConfirmation(sfWebRequest $request) {

    }

    public function executeMotDePasseOublie(sfWebRequest $request) {

    }

    public function executeModification(sfWebRequest $request) {
        $this->etablissement = $this->getUser()->getEtablissement();

        $this->form = new EtablissementModificationEmailForm($this->etablissement);

        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->etablissement = $this->form->save();
                $this->getUser()->setFlash('maj', 'Vos identifiants ont bien été mis à jour.');
                $this->redirect('mon_compte');
            }
        }
    }

}

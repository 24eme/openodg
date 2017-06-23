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

    public function executeRedirectToMonCompteCiva(sfWebRequest $request) {
        if($request->getParameter('return_mon_compte')) {
            return $this->redirect(sprintf("%s?%s", sfConfig::get('app_url_compte_mot_de_passe'), http_build_query(array('service' => $this->generateUrl("mon_compte", array(), true)))));
        }

        return $this->redirect(sprintf("%s?%s", sfConfig::get('app_url_compte_mot_de_passe'), http_build_query(array('service' => $this->generateUrl("accueil", array(), true)))));
    }

}

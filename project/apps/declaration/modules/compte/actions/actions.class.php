<?php

class compteActions extends sfActions {

    public function executeCreationAdmin(sfWebRequest $request) {
        $this->compte = new Compte();

        $this->form = new CompteModificationForm($this->compte);

        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->compte = $this->form->save();
                $this->getUser()->setFlash('maj', 'Le compte a bien été mis à jour.');
                $this->redirect('home');
            }
        }
    }

    public function executeVisualisationAdmin(sfWebRequest $request) {
        $this->compte = $this->getRoute()->getCompte();
    }

    public function executeModificationAdmin(sfWebRequest $request) {
        $this->compte = $this->getRoute()->getCompte();
        $this->form = new CompteModificationForm($this->compte);

        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $this->compte = $this->form->save();
                $this->getUser()->setFlash('maj', 'Le compte a bien été mis à jour.');
                $this->redirect('home');
            }
        }
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
                $this->redirect('@mon_compte');
            }
        }
    }

    public function executeRedirectToMonCompteCiva(sfWebRequest $request) {
        $url_compte_civa = sfConfig::get('app_url_compte_mot_de_passe');
        return $this->redirect($url_compte_civa);
    }

}

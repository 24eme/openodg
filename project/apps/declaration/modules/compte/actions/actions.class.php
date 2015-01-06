<?php

class compteActions extends sfActions {

    public function executeChoiceCreationAdmin(sfWebRequest $request) {

        $this->form = new CompteChoiceCreationForm();
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $type_compte = $this->form->getValue("type_compte");
                $this->redirect('compte_creation_admin', array("type_compte" => $type_compte));
            }
        }
    }

    public function executeCreationAdmin(sfWebRequest $request) {
        $this->type_compte = $request->getParameter('type_compte');
        if (!$this->type_compte) {
            throw sfException("La création de compte doit avoir un type");
        }
        $this->compte = new Compte($this->type_compte);
        $this->form = $this->getCompteModificationForm();

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

        if ($this->compte->isTypeCompte(CompteClient::TYPE_COMPTE_CONTACT)) {
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
    }

    public function executeModificationEtablissementAdmin(sfWebRequest $request) {
        $this->etablissement = $this->getUser()->getEtablissement();
        $this->compte = $this->etablissement->getCompte();
        if (!$this->compte) {
            throw new sfException("L'etablissement " . $this->etablissement->identifiant . " n'a pas de compte");
        }
        $this->redirect('compte_modification_admin', array('id' => $this->compte->identifiant));
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


    public function executeRecherche(sfWebRequest $request) {
    	$this->form = new CompteRechercheForm();
    	$this->form->bind($request->getParameter($this->form->getName()));
        if ($this->form->isValid()) {
        	
        }
        $test = new acElasticaQueryMatchAll();
    }

    private function getCompteModificationForm() {
        switch ($this->compte->getTypeCompte()) {
            case CompteClient::TYPE_COMPTE_CONTACT:
                return new CompteContactModificationForm($this->compte);
            case CompteClient::TYPE_COMPTE_ETABLISSEMENT:
                return new CompteEtablissementModificationForm($this->compte);
            case CompteClient::TYPE_COMPTE_DEGUSTATEUR:
                return new CompteDegustateurModificationForm($this->compte);
            case CompteClient::TYPE_COMPTE_AGENT_PRELEVEMENT:
                return new CompteAgentPrelevementModificationForm($this->compte);
        }
    }

}

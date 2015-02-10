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

        $this->prelevements = DegustationClient::getInstance()->getPrelevements($this->degustation->date_prelevement_debut, $this->degustation->date_prelevement_fin);

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

        $this->prelevements = DegustationClient::getInstance()->getPrelevements($this->degustation->date_prelevement_debut, $this->degustation->date_prelevement_fin);
    }

    public function executeDegustateurs(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();

        return $this->redirect('degustation_degustateurs_type', array('sf_subject' => $this->degustation, 'type' => CompteClient::ATTRIBUT_DEGUSTATEUR_PORTEUR_MEMOIRES));
    }

    public function executeDegustateursType(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();

        $this->types = CompteClient::getInstance()->getAttributsForType(CompteClient::TYPE_COMPTE_DEGUSTATEUR);

        $this->type = $request->getParameter('type', null);
        $this->type_previous = null;
        $this->type_next = null;

        foreach($this->types as $type_key => $type_libelle) {
            if($type_key != $request->getParameter('type', null)) {
                $this->type_previous = $type_key;
                continue;
            }

            $this->type = $type_key;
            $this->type_next = key($this->types);

            break;
        }

        if(!$this->type) {

            throw new sfException(sprintf("Le type de dégustateur \"%s\" est introuvable", $request->getParameter('type', null)));
        }

        $this->degustateurs = DegustationClient::getInstance()->getDegustateurs($this->type);
    }

    public function executeAgents(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();

        $this->agents = DegustationClient::getInstance()->getAgents();

        $this->jours = array();
        $date = new DateTime($this->degustation->date);
        $date->modify('-7 days');

        for($i=1; $i <= 7; $i++) {
            $this->jours[] = $date->format('Y-m-d');
            $date->modify('+ 1 day');
        }
    }

    public function executePrelevements(sfWebRequest $request) {

    }

    public function executeValidation(sfWebRequest $request) {
 
    }

    public function executeTournee(sfWebRequest $request) {

    }

    public function executeAffectation(sfWebRequest $request) {

    }

    public function executeDegustation(sfWebRequest $request) {
        
    }
}

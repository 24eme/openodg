<?php

class constatsActions extends sfActions {

    public function executeIndex(sfWebRequest $request) {
        $this->getUser()->signOutEtablissement();

        $this->organisationJournee = $this->buildOrganisationJournee();
        $this->form = new LoginForm();

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }
        $this->getUser()->signInEtablissement($this->form->getValue('etablissement'));

        return $this->redirect('rendezvous_declarant', $this->getUser()->getEtablissement()->getCompte());
    }

    public function executeRendezvousDeclarant(sfWebRequest $request) {
        $this->compte = $this->getRoute()->getCompte();
        $this->rendezvousDeclarant = RendezvousClient::getInstance()->getRendezvousByCompte($this->compte->cvi);
        $this->formsRendezVous = array();
        $this->form = new LoginForm();
        
        foreach ($this->compte->getChais() as $chaiKey => $chai) {
            $rendezvous = new Rendezvous();
            $rendezvous->identifiant = $this->compte->identifiant;
            $rendezvous->idchai = $chaiKey;
            $this->formsRendezVous[$chaiKey] = new RendezvousDeclarantForm($rendezvous);
        }

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }
         $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }
        $this->getUser()->signInEtablissement($this->form->getValue('etablissement'));

        return $this->redirect('rendezvous_declarant', $this->getUser()->getEtablissement()->getCompte());
    }

    public function executeRendezvousModification(sfWebRequest $request) {
        $this->rendezvous = $this->getRoute()->getRendezvous();
        $this->chai = $this->rendezvous->getChai();
        $this->form = new RendezvousDeclarantForm($this->rendezvous);
        if (!$request->isMethod(sfWebRequest::POST)) {
            return sfView::SUCCESS;
        }
        $this->form->bind($request->getParameter($this->form->getName()));
        if (!$this->form->isValid()) {
            return $this->getTemplate('rendezvousDeclarant');
        }
        $this->form->save();
        $this->redirect('rendezvous_declarant', $this->rendezvous->getCompte());
    }

    public function executeRendezvousCreation(sfWebRequest $request) {
        $this->compte = $this->getRoute()->getCompte();
        $this->idchai = $request->getParameter('idchai');
        $rendezvous = new Rendezvous();
        $rendezvous->idchai = $this->idchai;
        $this->form = new RendezvousDeclarantForm($rendezvous);

        if (!$request->isMethod(sfWebRequest::POST)) {
            return sfView::SUCCESS;
        }
        $this->form->bind($request->getParameter($this->form->getName()));
        if (!$this->form->isValid()) {
            return $this->getTemplate('rendezvousDeclarant');
        }
        $date = $this->form->getValue('date');
        $heure = $this->form->getValue('heure');
        $commentaire = $this->form->getValue('commentaire');
        $rendezvous = RendezvousClient::getInstance()->findOrCreate($this->compte, $this->idchai, $date, $heure, $commentaire);
        $rendezvous->save();
        $this->redirect('rendezvous_declarant', $this->compte);
    }

    public function buildOrganisationJournee() {
        $dateToday = date('Y-m-d');
        $organisationJournee = array();
        $dates = array(Date::addDelaiToDate("-2 day", $dateToday),
            Date::addDelaiToDate("-1 day", $dateToday),
            $dateToday,Date::addDelaiToDate("+1 day", $dateToday), Date::addDelaiToDate("+2 day", $dateToday));
        foreach ($dates as $date) {
            $organisationJournee[$date] = array();
            $resultsDate = DocAllByTypeAndDateView::getInstance()->allByTypeAndDate('Rendezvous',$date);
            foreach ($resultsDate as $resultDate){
                if(!array_key_exists($resultDate->value->statut, $organisationJournee[$date])){
                    $organisationJournee[$date][$resultDate->value->statut] = array();
                }
                $organisationJournee[$date][$resultDate->value->statut][$resultDate->id] = $resultDate;
            }
        }
        return $organisationJournee;
    }

}

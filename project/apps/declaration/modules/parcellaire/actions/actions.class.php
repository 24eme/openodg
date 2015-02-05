<?php

class parcellaireActions extends sfActions {

    public function executeIndex(sfWebRequest $request) {
        $this->getUser()->signOutEtablissement();
        $this->form = new LoginForm();
        $this->getUser()->signInEtablissement($this->form->getValue('etablissement'));
        if (!$request->isMethod(sfWebRequest::POST)) {
            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }


        return $this->redirect('home');
    }

    public function executeCreation(sfWebRequest $request) {
        $this->etablissementIdentifiant = $request->getParameter('identifiant');

        if (!$this->etablissementIdentifiant) {
            throw new sfException("L'identifiant de l'etablissement est obligatoire pour créer un parcellaire");
        }
        $this->etablissement = EtablissementClient::getInstance()->findByIdentifiant($this->etablissementIdentifiant);
        if (!$this->etablissement) {
            throw new sfException("L'etablissement n'a pas été trouvé");
        }

        $campagneManager = new CampagneManager('08-01', CampagneManager::FORMAT_PREMIERE_ANNEE);
        $this->campagne = $campagneManager->getCurrent();

        $this->parcellaire = ParcellaireClient::getInstance()->findOrCreate($this->etablissement, $this->campagne);
        $this->parcellaire->save();
        $this->redirect('parcellaire_exploitation', $this->parcellaire);
    }

    public function executeExploitation(sfWebRequest $request) {
        $this->parcellaire = $this->getRoute()->getParcellaire();
        //  $this->secure(DRevSecurity::EDITION, $this->parcellaire);
        $this->parcellaire->storeEtape($this->getEtape($this->parcellaire, ParcellaireEtapes::ETAPE_EXPLOITATION));
        $this->parcellaire->save();
        $this->etablissement = $this->parcellaire->getEtablissementObject();
        $this->form = new EtablissementForm($this->etablissement, array("use_email" => !$this->parcellaire->isPapier()));
        $this->parcellaireTypeProprietaireForm = new ParcellaireExploitationTypeProprietaireForm($this->parcellaire);
        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        $this->parcellaire->storeDeclarant();

        $this->parcellaire->save();
        if ($request->getParameter('redirect', null)) {
            return $this->redirect('parcellaire_validation', $this->parcellaire);
        }

//        if (!$this->parcellaire->isNonRecoltant() && !$this->drev->hasDr() && !$this->drev->isPapier()) {
//
//            return $this->redirect('drev_dr', $this->drev);
//        }

        return $this->redirect('parcellaire_parcelles', array('id' => $this->parcellaire->_id, 'appellation' => 'COMMUNALE'));
    }

    public function executeParcelles(sfWebRequest $request) {
        $this->parcellaire = $this->getRoute()->getParcellaire();
        $this->appellation = $request->getParameter('appellation');
    }

    public function executeAcheteurs(sfWebRequest $request) {
        
    }

    public function executeValidation(sfWebRequest $request) {
        
    }

    protected function getEtape($parcellaire, $etape) {
        $parcellaireEtapes = ParcellaireEtapes::getInstance();
        if (!$parcellaire->exist('etape')) {
            return $etape;
        }
        return ($parcellaireEtapes->isLt($parcellaire->etape, $etape)) ? $etape : $parcellaire->etape;
    }

}

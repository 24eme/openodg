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

    public function executeCreate(sfWebRequest $request) {
        
        $etablissement = $this->getRoute()->getEtablissement();
        $this->parcellaire = ParcellaireClient::getInstance()->findOrCreate($etablissement->cvi, ConfigurationClient::getInstance()->getCampagneManager()->getCurrent());
        $this->parcellaire->save();

        return $this->redirect('parcellaire_edit', $this->parcellaire);        
    }
    
    public function executeEdit(sfWebRequest $request) {
        $parcellaire = $this->getRoute()->getParcellaire();

        if ($parcellaire->exist('etape') && $parcellaire->etape) {
            return $this->redirect('parcellaire_' . $parcellaire->etape, $parcellaire);
        }

        return $this->redirect('parcellaire_exploitation', $parcellaire);
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

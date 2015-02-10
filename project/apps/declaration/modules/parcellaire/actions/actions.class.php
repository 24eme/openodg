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

        //   $this->secure(ParcellaireSecurity::EDITION, $this->parcellaire);

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
        return $this->redirect('parcellaire_exploitation', $this->parcellaire);
    }

    public function executePropriete(sfWebRequest $request) {
        if (!$request->isMethod(sfWebRequest::POST)) {
            throw new sfException("NO POST");
        }
        $this->parcellaire = $this->getRoute()->getParcellaire();
        $this->etablissement = $this->parcellaire->getEtablissementObject();
        $this->form = new EtablissementForm($this->etablissement, array("use_email" => !$this->parcellaire->isPapier()));

        $this->parcellaireTypeProprietaireForm = new ParcellaireExploitationTypeProprietaireForm($this->parcellaire);

        $this->parcellaireTypeProprietaireForm->bind($request->getParameter($this->parcellaireTypeProprietaireForm->getName()));
        if (!$this->parcellaireTypeProprietaireForm->isValid()) {
            throw new sfException("form no valid");
        }
        $this->parcellaireTypeProprietaireForm->save();

        $this->parcellaire->save();
        $this->firstAppellation = $this->parcellaire->getFirstAppellation();
        return $this->redirect('parcellaire_parcelles', array('id' => $this->parcellaire->_id, 'appellation' => $this->firstAppellation));
    }

    public function executeParcelles(sfWebRequest $request) {
        $this->parcellaire = $this->getRoute()->getParcellaire();
        $this->parcellaire->initProduitFromLastParcellaire();
        $this->parcellaireAppellations = ParcellaireClient::getInstance()->getAppellationsKeys();
        $this->appellation = $request->getParameter('appellation');

        $allParcellesByAppellations = $this->parcellaire->getAllParcellesByAppellations();
        $this->parcelles = array();
        foreach ($allParcellesByAppellations as $appellation) {
            $appellationKey = str_replace('appellation_', '', $appellation->appellation->getKey());
            if ($this->appellation == $appellationKey) {
                $this->parcelles = $appellation->parcelles;
            }
        }

        $this->form = new ParcellaireAppellationEditForm($this->parcellaire, $this->appellation, $this->parcelles);
        if ($request->isMethod(sfWebRequest::POST)) {
            $this->form->bind($request->getParameter($this->form->getName()));

            if ($this->form->isValid()) {
                $this->form->save();
            }
        }
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

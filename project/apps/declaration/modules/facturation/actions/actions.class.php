<?php

class facturationActions extends sfActions 
{
	
    public function executeIndex(sfWebRequest $request) 
    {
        $this->generations = GenerationClient::getInstance()->findHistoryWithType(GenerationClient::TYPE_DOCUMENT_FACTURES,100);

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

        return $this->redirect('facturation_declarant', $this->getUser()->getEtablissement()->getCompte()); 
    }

    public function executeMassive(sfWebRequest $request) 
    {
        $this->generation = new Generation();
        $this->generation->type_document = GenerationClient::TYPE_DOCUMENT_FACTURES;
            

        $defaults = array();
        if($request->getParameter('q')) {
            $defaults['requete'] = $request->getParameter('q');
        }

        $this->form = new FacturationMassiveForm($this->generation, $defaults, array('modeles' => ConfigurationClient::getConfiguration('2014')->getTemplatesFactures()));
        
        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));
            
        if(!$this->form->isValid()) {
            return sfView::SUCCESS;
        }

        $this->form->updateDocument();
        $this->generation->save();

        return $this->redirect('generation_view', array('type_document' => GenerationClient::TYPE_DOCUMENT_FACTURES, 'date_emission' => $this->generation->date_emission));
    }

    public function executeEdition(sfWebRequest $request) {
        $this->facture = FactureClient::getInstance()->find($request->getParameter('id'));
        $this->form = new FactureEditionForm($this->facture);

         if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));
            
        if(!$this->form->isValid()) {
            return sfView::SUCCESS;
        }

        $this->form->save();

        $this->getUser()->setFlash("notice", "La facture a été modifiée.");
        
        return $this->redirect('facturation_declarant', array("id" => "COMPTE-".$this->facture->identifiant));
    }

    public function executeLatex(sfWebRequest $request) {
        
        $this->setLayout(false);
        $this->facture = FactureClient::getInstance()->find($request->getParameter('id'));
        $this->forward404Unless($this->facture);
        $latex = new FactureLatex($this->facture);
        $latex->echoWithHTTPHeader($request->getParameter('type'));
        exit;
    }

    public function executeRegenerate(sfWebRequest $request) {
        $facture = FactureClient::getInstance()->find($request->getParameter('id'));

        $f = FactureClient::getInstance()->regenerate($facture);
        $f->save();

        $this->getUser()->setFlash("notice", "La facture a été regénérée.");

        return $this->redirect('facturation_declarant', array("id" => "COMPTE-".$f->identifiant));
    }

    public function executeDeclarant(sfWebRequest $request) {
        $this->compte = $this->getRoute()->getCompte();
        $this->factures = FactureClient::getInstance()->getFacturesByCompte($this->compte->identifiant, acCouchdbClient::HYDRATE_JSON);
        $this->values = array();
        $this->templatesFactures = ConfigurationClient::getConfiguration('2014')->getTemplatesFactures();
        $this->form = new FacturationDeclarantForm($this->templatesFactures);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }
        
        $this->form->bind($request->getParameter($this->form->getName()));
            
        if(!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->values = $this->form->getValues();
        $templateFacture = TemplateFactureClient::getInstance()->find($this->values['modele']);
        
        try {
            $generation = FactureClient::getInstance()->createFactureByCompte($templateFacture, $this->compte->_id);
        } catch (Exception $e) {
            $this->getUser()->setFlash("error", $e->getMessage());

            return $this->redirect('facturation_declarant', $this->compte);
        }

        if(!$generation) {
            $this->getUser()->setFlash("error", "Cet opérateur a déjà été facturé pour ce type de facture.");

            return $this->redirect('facturation_declarant', $this->compte);
        }

        $generation->save();

        return $this->redirect('generation_view', array('type_document' => GenerationClient::TYPE_DOCUMENT_FACTURES, 'date_emission' => $generation->date_emission));
    }
        
    private function getLatexTmpPath() {
            return "/tmp/";
    }

}

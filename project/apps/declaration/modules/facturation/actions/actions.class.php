<?php

class facturationActions extends sfActions 
{
	
    public function executeIndex(sfWebRequest $request) 
    {
    	
    	$this->values = array();
    	$this->templatesFactures = ConfigurationClient::getConfiguration('2014')->getTemplatesFactures();
    	$this->form = new FacturationForm($this->templatesFactures);
        $this->generations = GenerationClient::getInstance()->findHistoryWithType(GenerationClient::TYPE_DOCUMENT_FACTURES,10);
    	
    	if ($request->isMethod(sfWebRequest::POST)) {
    		$this->form->bind($request->getParameter($this->form->getName()));
    		
	    	if($this->form->isValid()) {

	    		$this->values = $this->form->getValues();
	       		$compte = CompteClient::getInstance()->find($this->values['declarant']);
	       		$templateFacture = TemplateFactureClient::getInstance()->find($this->values['template_facture']);
	       		$generation = FactureClient::getInstance()->createFactureByCompte($templateFacture, $compte->_id);
                $generation->save();
                return $this->redirect('generation_view', array('type_document' => GenerationClient::TYPE_DOCUMENT_FACTURES, 'date_emission' => $generation->date_emission));
	    	}
        }
    }

    public function executeMassive(sfWebRequest $request) 
    {
        
        $this->generation = new Generation();
        $this->generation->type_document = GenerationClient::TYPE_DOCUMENT_FACTURES;
        
        $this->form = new FacturationMassiveForm($this->generation, array(), array('modeles' => ConfigurationClient::getConfiguration('2014')->getTemplatesFactures()));
        
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

    public function executeLatex(sfWebRequest $request) {
        
        $this->setLayout(false);
        $this->facture = FactureClient::getInstance()->find($request->getParameter('id'));
        $this->forward404Unless($this->facture);
        $latex = new FactureLatex($this->facture);
        $latex->echoWithHTTPHeader($request->getParameter('type'));
        exit;
    }
        
    private function getLatexTmpPath() {
            return "/tmp/";
    }
}

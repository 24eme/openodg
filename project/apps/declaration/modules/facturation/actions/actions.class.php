<?php

class facturationActions extends sfActions 
{
	
    public function executeIndex(sfWebRequest $request) 
    {
    	
    	$this->values = array();
    	$this->templatesFactures = ConfigurationClient::getConfiguration('2014')->getTemplatesFactures();
    	$this->form = new FacturationForm($this->templatesFactures);
    	
    	if ($request->isMethod(sfWebRequest::POST)) {
    		$this->form->bind($request->getParameter($this->form->getName()));
    		
	    	if($this->form->isValid()) {

	    		$this->values = $this->form->getValues();
	       		$compte = CompteClient::getInstance()->findByIdentifiant($this->values['declarant']);
	       		$templateFacture = TemplateFactureClient::getInstance()->find($this->values['template_facture']);
	       		$cotisations = $templateFacture->generateCotisations($compte->cvi, $templateFacture->campagne);
            	$facture = FactureClient::getInstance()->createDoc($cotisations, $compte);
            	$facture->save();
	    	}
        }
    }

    

}

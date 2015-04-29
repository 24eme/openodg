<?php

class facturationActions extends sfActions 
{
	
    public function executeIndex(sfWebRequest $request) 
    {
    	$this->form = new FacturationForm();
    	$this->values = array();
    	
    	if ($request->isMethod(sfWebRequest::POST)) {
    		$this->form->bind($request->getParameter($this->form->getName()));
	    	if($this->form->isValid()) {
	        	$this->values = $this->form->getValues();
	        }
        }
    }

    

}

<?php

class constatsActions extends sfActions {

  public function executeIndex(sfWebRequest $request) 
    {
        $this->constats = array();
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
    
}

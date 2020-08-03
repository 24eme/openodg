<?php

class degustationActions extends sfActions {

    public function executeIndex(sfWebRequest $request) {
        
        $this->form = new DegustationCreationForm(new Degustation());
        
        if (!$request->isMethod(sfWebRequest::POST)) {
        
            return sfView::SUCCESS;
        }
        
        $this->form->bind($request->getParameter($this->form->getName()));
        
        if (!$this->form->isValid()) {
        
            return sfView::SUCCESS;
        }
        
        $this->form->save();
    }
    
    public function executeDeclarant(sfWebRequest $request) {
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->degustations = DegustationClient::getInstance()->getDegustationsByEtablissement($this->etablissement->identifiant);
    }

    
}

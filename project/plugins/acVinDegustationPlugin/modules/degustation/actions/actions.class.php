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
        
        $degustation = $this->form->save();
        
        return $this->redirect('degustation_prelevement_lots', $degustation);
    }
    
    public function executePrelevementLots(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        
        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_LOTS))) {
            $this->degustation->save();
        }
        
        $this->form = new DegustationPrelevementLotsForm($this->degustation);
        
        if (!$request->isMethod(sfWebRequest::POST)) {
        
            return sfView::SUCCESS;
        }
        
        $this->form->bind($request->getParameter($this->form->getName()));
        
        if (!$this->form->isValid()) {
        
            return sfView::SUCCESS;
        }
        
        $this->form->save();
        
        return $this->redirect('degustation_validation', $this->degustation);
    }
    
    public function executeValidation(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        
        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_VALIDATION))) {
            $this->degustation->save();
        }
    }

    protected function getEtape($doc, $etape, $class = "DegustationEtapes") {
        $etapes = $class::getInstance();
        if (!$doc->exist('etape')) {
            return $etape;
        }
        return ($etapes->isLt($doc->etape, $etape)) ? $etape : $doc->etape;
    }

    
}

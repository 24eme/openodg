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
        
        return ($next = $this->getRouteNextEtape())? $this->redirect($next, $degustation) : $this->redirect('degustation');
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
        
        return ($next = $this->getRouteNextEtape($this->degustation->etape))? $this->redirect($next, $this->degustation) : $this->redirect('degustation');
    }
    
    public function executeSelectionDegustateurs(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        
        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_DEGUSTATEURS))) {
            $this->degustation->save();
        }
        
        
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
    
    protected function getRouteNextEtape($etape = null, $class = "DegustationEtapes") {
        $etapes = $class::getInstance();
        $routes = $etapes->getRouteLinksHash();
        if (!$etape) {
            $etape = $etapes->getFirst();
        } else {
            $etape = $etapes->getNext($etape);
        }
        return (isset($routes[$etape]))? $routes[$etape] : null; 
    }

    
}

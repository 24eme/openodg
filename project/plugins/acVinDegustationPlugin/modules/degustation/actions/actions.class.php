<?php

class degustationActions extends sfActions {

    public function executeIndex(sfWebRequest $request) {        
        $this->form = new DegustationCreationForm(new Degustation());

        $this->degustations = DegustationClient::getInstance()->getHistory();

        if (!$request->isMethod(sfWebRequest::POST)) {
        
            return sfView::SUCCESS;
        }
        
        $this->form->bind($request->getParameter($this->form->getName()));
        
        if (!$this->form->isValid()) {
        
            return sfView::SUCCESS;
        }
        
        $degustation = $this->form->save();
        
        return $this->redirect('degustation_redirect', $degustation);
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
        
        return ($next = $this->getRouteNextEtape(DegustationEtapes::ETAPE_LOTS))? $this->redirect($next, $this->degustation) : $this->redirect('degustation');
    }
    
    public function executeSelectionDegustateurs(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        
        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_DEGUSTATEURS))) {
            $this->degustation->save();
        }
        
        $this->form = new DegustationSelectionDegustateursForm($this->degustation);
        
        if (!$request->isMethod(sfWebRequest::POST)) {
        
            return sfView::SUCCESS;
        }
        
        $this->form->bind($request->getParameter($this->form->getName()));
        
        if (!$this->form->isValid()) {
        
            return sfView::SUCCESS;
        }
        
        $this->form->save();
        
        return ($next = $this->getRouteNextEtape(DegustationEtapes::ETAPE_DEGUSTATEURS))? $this->redirect($next, $this->degustation) : $this->redirect('degustation');
    }
    
    public function executeValidation(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        
        if ($this->degustation->storeEtape($this->getEtape($this->degustation, DegustationEtapes::ETAPE_VALIDATION))) {
            $this->degustation->save();
        }
        
        $this->form = new DegustationValidationForm($this->degustation);
        
        if (!$request->isMethod(sfWebRequest::POST)) {
        
            return sfView::SUCCESS;
        }
        
        $this->form->bind($request->getParameter($this->form->getName()));
        
        if (!$this->form->isValid()) {
        
            return sfView::SUCCESS;
        }
        
        $this->form->save();
        
        return $this->redirect('degustation_visualisation', $this->degustation);
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
    }

    public function executeDevalidation(sfWebRequest $request) {
        $this->degustation = $this->getRoute()->getDegustation();
        $this->degustation->devalidate();
        $this->degustation->save();
    
        $this->getUser()->setFlash("notice", "La déclaration a été dévalidé avec succès.");
    
        return $this->redirect('degustation_validation', $this->degustation);
    }
    
    public function executeRedirect(sfWebRequest $request) {
        $degustation = $this->getRoute()->getDegustation();

        if ($degustation->isValidee()) {
            return $this->redirect('degustation_visualisation', $degustation);
        }
        
        return ($next = $this->getRouteNextEtape($degustation->etape))? $this->redirect($next, $degustation) : $this->redirect('degustation');
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

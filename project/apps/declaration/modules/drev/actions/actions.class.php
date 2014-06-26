<?php

class drevActions extends sfActions
{

    public function executeCreate(sfWebRequest $request)
    {
    	if ($drev = DRevClient::getInstance()->find('DREV-7523700100-2013-2014')) {
    		$drev->delete();
    	}
        $drev = DRevClient::getInstance()->createDrev('7523700100', '2013-2014');
        $drev->save();

        return $this->redirect('drev_edit', $drev);
    }

    public function executeEdit(sfWebRequest $request)
    {
        $drev = $this->getRoute()->getDRev();

        return $this->redirect('drev_revendication', $drev);
    }

    public function executeRevendication(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->form = new DRevRevendicationForm($this->drev);
        if ($request->isMethod(sfWebRequest::POST)) {
    		$this->form->bind($request->getParameter($this->form->getName()));
        	if ($this->form->isValid()) {
        		$this->form->save();
        		return $this->redirect('drev_degustation_conseil', $this->drev);
        	}
        }
    }

    public function executeDegustationConseil(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();

        $this->form = new DRevDegustationConseilForm($this->drev->prelevements);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect('drev_lots', array("sf_subject" => $this->drev, "cuve" => Drev::NODE_CUVE_ALSACE));
    }

    public function executeLots(sfWebRequest $request) {
        $this->cuve = $request->getParameter('cuve');
        if(!in_array($this->cuve, array(Drev::NODE_CUVE_ALSACE, Drev::NODE_CUVE_GRDCRU))) {
            
            return $this->forward404();
        }
        $this->drev = $this->getRoute()->getDRev();
        $this->lot = $this->drev->lots->add($this->cuve);
		$this->form = new DRevLotsForm($this->lot);
		$this->ajoutForm = new DrevLotsAjoutProduitForm($this->lot);

        $this->setTemplate(lcfirst(sfInflector::camelize(strtolower(('lots_'.$this->cuve)))));

    	if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }
        
    	$this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {
            return sfView::SUCCESS;
        }
        
		$this->form->save();

        if($this->cuve == Drev::NODE_CUVE_ALSACE) {
            return $this->redirect('drev_lots', array("sf_subject" => $this->drev, "cuve" => Drev::NODE_CUVE_GRDCRU));
        }
		
        return $this->redirect('drev_controle_externe', $this->drev);
    }
    
    public function executeLotsAjoutProduit(sfWebRequest $request) {
    	$this->cuve = $request->getParameter('cuve');
        if(!in_array($this->cuve, array(Drev::NODE_CUVE_ALSACE, Drev::NODE_CUVE_GRDCRU))) {
            
            return $this->forward404();
        }
        
    	$this->drev = $this->getRoute()->getDRev();
        $this->lot = $this->drev->lots->add($this->cuve);
    	$this->ajoutForm = new DrevLotsAjoutProduitForm($this->lot);
    	$this->ajoutForm->bind($request->getParameter($this->ajoutForm->getName()));

        $url = $this->generateUrl('drev_lots', array('sf_subject' => $this->drev, 'cuve' => $this->cuve));

        if(!$this->ajoutForm->isValid()) {
            $this->getUser()->setFlash("erreur", 'Une erreur est survenue.');
            
            return $this->redirect($url);
        } 
        
        $this->ajoutForm->save();
        $this->getUser()->setFlash("notice", 'Le produit a été ajouté avec succès.');
        
        return $this->redirect($url);
    }

    public function executeControleExterne(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();

        $this->form = new DRevControleExterneForm($this->drev->prelevements);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect('drev_validation', $this->drev);
    }

    public function executeValidation(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->validation = new DrevValidation($this->drev);
    }

    public function executePDF(sfWebRequest $request) {
        $drev = $this->getRoute()->getDRev();

        $this->document = new ExportDRevPdf($drev, $this->getRequestParameter('output', 'pdf'), false);
        $this->document->setPartialFunction(array($this, 'getPartial'));

        if($request->getParameter('force')) {
            $this->document->removeCache();
        }
        
        $this->document->generate();

        $this->document->addHeaders($this->getResponse());

        return $this->renderText($this->document->output());
    }
}

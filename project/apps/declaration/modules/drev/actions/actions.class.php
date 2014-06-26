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

        return $this->redirect('drev_lots_alsace', $this->drev);
    }

    public function executeLotsAlsace(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->lot = $this->drev->lots->add(DRev::NODE_CUVE_ALSACE);
		$this->form = new DRevLotsForm($this->lot);
		$this->ajoutForm = new DrevLotsAjoutProduitForm($this->lot);
    	if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }
        
    	$this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {
            return sfView::SUCCESS;
        }
        
		$this->form->save();
		
        return $this->redirect('drev_lots_grdcru', $this->drev);
    }

    public function executeLotsGrdCru(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->lot = $this->drev->lots->add(DRev::NODE_CUVE_GRDCRU);
        $this->form = new DRevLotsForm($this->lot);
		$this->ajoutForm = new DrevLotsAjoutProduitForm($this->lot);
    	if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }
        
    	$this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {
            return sfView::SUCCESS;
        }
        
		$this->form->save();
		
        return $this->redirect('drev_controle_externe', $this->drev);
    }
    
    public function executeLotsAjoutProduit(sfWebRequest $request) {
    	$this->forward404Unless($this->cuve = $request->getParameter('cuve'));
    	$this->drev = $this->getRoute()->getDRev();
        $this->lot = $this->drev->lots->add($this->cuve);
    	$this->ajoutForm = new DrevLotsAjoutProduitForm($this->lot);
    	$this->ajoutForm->bind($request->getParameter($this->ajoutForm->getName()));

        $url = 'drev_lots_'.strtolower(str_replace(DRev::PREFIXE_LOT_KEY, '', $this->cuve));

        if(!$this->ajoutForm->isValid()) {
            $this->getUser()->setFlash("erreur", 'Une erreur est survenue.');
            
            return $this->redirect($url, $this->drev);
        } 
        
        $this->ajoutForm->save();
        $this->getUser()->setFlash("notice", 'Le produit a été ajouté avec succès.');
        
        return $this->redirect($url, $this->drev);
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

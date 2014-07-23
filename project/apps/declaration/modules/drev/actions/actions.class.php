<?php

class drevActions extends sfActions
{

    public function executeIndex(sfWebRequest $request)
    {

    }
 
    public function executeCreate(sfWebRequest $request)
    {
    	if ($drev = DRevClient::getInstance()->find('DREV-7523700100-2013-2014')) {
    		$drev->delete();
    	}
        $drev = DRevClient::getInstance()->createDoc('7523700100', '2013-2014');
        $drev->save();

        return $this->redirect('drev_edit', $drev);
    }

    public function executeEdit(sfWebRequest $request)
    {
        $drev = $this->getRoute()->getDRev();

        return $this->redirect('drev_exploitation', $drev);
    }

    public function executeDelete(sfWebRequest $request)
    {
        $drev = $this->getRoute()->getDRev();
		$drev->delete();	
		$this->getUser()->setFlash("notice", 'La DRev a été supprimé avec succès.');	
        return $this->redirect($this->generateUrl('home') . '#drev');
    }

    public function executeExploitation(sfWebRequest $request)
    {
        $this->drev = $this->getRoute()->getDRev();
        $this->etablissement = $this->drev->getEtablissementObject();

        $this->form = new EtablissementForm($this->etablissement);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        $this->drev->storeDeclarant();
        $this->drev->save();

        return $this->redirect('drev_revendication', $this->drev);
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

        return $this->redirect('drev_lots', $this->drev->addPrelevement(Drev::CUVE_ALSACE));
    }

    public function executeLots(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->prelevement = $this->getRoute()->getPrelevement();

		$this->form = new DRevLotsForm($this->prelevement);
		$this->ajoutForm = new DrevLotsAjoutProduitForm($this->prelevement);

        $this->setTemplate(lcfirst(sfInflector::camelize(strtolower(('lots_'.$this->prelevement->getKey())))));

    	if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }
        
    	$this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {
            return sfView::SUCCESS;
        }
        
		$this->form->save();

        if($this->prelevement->getKey() == Drev::CUVE_ALSACE) {
            return $this->redirect('drev_lots', $this->drev->addPrelevement(Drev::CUVE_GRDCRU));
        }
		
        return $this->redirect('drev_controle_externe', $this->drev);
    }

    public function executeLotsAjoutProduit(sfWebRequest $request) {
    	$this->drev = $this->getRoute()->getDRev();
        $this->prelevement = $this->getRoute()->getPrelevement();

    	$this->ajoutForm = new DrevLotsAjoutProduitForm($this->prelevement);
    	$this->ajoutForm->bind($request->getParameter($this->ajoutForm->getName()));

        if(!$this->ajoutForm->isValid()) {
            $this->getUser()->setFlash("erreur", 'Une erreur est survenue.');
            
            return $this->redirect('drev_lots', $this->prelevement);
        } 
        
        $this->ajoutForm->save();
        $this->getUser()->setFlash("notice", 'Le produit a été ajouté avec succès.');
        
        return $this->redirect('drev_lots', $this->prelevement);
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
        $this->validation = new DRevValidation($this->drev);
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

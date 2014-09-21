<?php

class drevActions extends sfActions
{

    public function executeIndex(sfWebRequest $request)
    {

    }  

    public function executePushDR(sfWebRequest $request) {
        $this->url = $request->getParameter('pull');
        $this->csv = base64_encode(file_get_contents(sfConfig::get('sf_data_dir').'/DR/DR-7523700100-2013.csv'));
        $this->pdf = base64_encode(file_get_contents(sfConfig::get('sf_data_dir').'/DR/DR-7523700100-2013.pdf'));
    }

    public function executeCreate(sfWebRequest $request)
    {
        $etablissement = $this->getRoute()->getEtablissement();
        $drev = DRevClient::getInstance()->createDoc($etablissement->identifiant, '2013-2014');
        $drev->save();

        return $this->redirect('drev_edit', $drev);
    }

    public function executeEdit(sfWebRequest $request)
    {
        $drev = $this->getRoute()->getDRev();

        return $this->redirect('drev_dr', $drev);
    }

    public function executeDelete(sfWebRequest $request)
    {
        $drev = $this->getRoute()->getDRev();
		$drev->delete();	
		$this->getUser()->setFlash("notice", 'La DRev a été supprimé avec succès.');	
        return $this->redirect($this->generateUrl('home') . '#drev');
    }

    public function executeDr(sfWebRequest $request)
    {
        $this->drev = $this->getRoute()->getDRev();

    }

    public function executeDrRecuperation(sfWebRequest $request) {
        $drev = $this->getRoute()->getDRev();

        return $this->redirect('drev_push_dr', array('pull' => $this->generateUrl('drev_dr_import', $drev)));
    }

    public function executeDrImport(sfWebRequest $request) {
        $drev = $this->getRoute()->getDRev();
        umask(0002);
        $cache_dir = sfConfig::get('sf_cache_dir').'/dr';
        if (!file_exists($cache_dir)) {
            mkdir($cache_dir);
        }

        file_put_contents($cache_dir."/DR.csv", base64_decode($request->getParameter('csv')));
        $drev->storeAttachment($cache_dir."/DR.csv", "text/csv");

        file_put_contents($cache_dir."/DR.pdf", base64_decode($request->getParameter('pdf')));
        $drev->storeAttachment($cache_dir."/DR.pdf", "application/pdf");

        $drev->updateFromCSV();
        $drev->save();

        return $this->redirect($this->generateUrl('drev_exploitation', $drev));
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

                return $this->redirect('drev_revendication_cepage', array('sf_subject' => $this->drev, 'hash' => $this->drev->declaration->getAppellations()->getFirst()->getKey()));
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

    public function executeRevendicationCepage(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->noeud = $this->drev->get("declaration/certification/genre/".$request->getParameter("hash"));
        $this->form = new DRevRevendicationCepageForm($this->noeud);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        if($this->noeud->getNextSister()) {
            
            return $this->redirect('drev_revendication_cepage', array('sf_subject' => $this->drev, 'hash' => $this->noeud->getNextSister()->getKey()));
        } else {

            return $this->redirect('drev_degustation_conseil', $this->drev);
        }
    }

    public function executeValidation(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
        $this->validation = new DRevValidation($this->drev);
        $this->form = new DRevValidationForm($this->drev);

        if(!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        if(!$this->validation->isValide()) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->drev->validate();
        $this->drev->save();

        return $this->redirect('drev_confirmation', $this->drev);
    }

    public function executeConfirmation(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
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

<?php

class drevmarcActions extends sfActions
{

    public function executeIndex(sfWebRequest $request)
    {

    }
 
    public function executeCreate(sfWebRequest $request)
    {
        $etablissement = $this->getRoute()->getEtablissement();
        $drevmarc = DRevMarcClient::getInstance()->createDoc($etablissement->identifiant, '2013-2014');
        $drevmarc->save();

        return $this->redirect('drevmarc_edit', $drevmarc);
    }

    public function executeEdit(sfWebRequest $request)
    {
        $drevmarc = $this->getRoute()->getDRevMarc();

        return $this->redirect('drevmarc_exploitation', $drevmarc);
    }

    public function executeDelete(sfWebRequest $request)
    {
        $drevmarc = $this->getRoute()->getDRevMarc();
		$drevmarc->delete();	
		$this->getUser()->setFlash("notice", 'La DRev a été supprimé avec succès.');	
        return $this->redirect($this->generateUrl('home') . '#drev');
    }

    public function executeExploitation(sfWebRequest $request)
    {
        $this->drevmarc = $this->getRoute()->getDRevMarc();
        $this->etablissement = $this->drevmarc->getEtablissementObject();

        $this->form = new EtablissementForm($this->etablissement);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        $this->drevmarc->storeDeclarant();
        $this->drevmarc->save();

        return $this->redirect('drevmarc_revendication', $this->drevmarc);
    }

    public function executeRevendication(sfWebRequest $request) {
        $this->drevmarc = $this->getRoute()->getDRevMarc();
        $this->form = new DRevMarcRevendicationForm($this->drevmarc);
        if ($request->isMethod(sfWebRequest::POST)) {
    		$this->form->bind($request->getParameter($this->form->getName()));
        	if ($this->form->isValid()) {
        		$this->form->save();
        		return $this->redirect('drevmarc_validation', $this->drevmarc);
        	}
        }
    }

    public function executeValidation(sfWebRequest $request) {
        $this->drevmarc = $this->getRoute()->getDRevMarc();
        $this->validation = new DRevMarcValidation($this->drevmarc);
        $this->form = new DRevMarcValidationForm($this->drevmarc);
        if ($request->isMethod(sfWebRequest::POST)) {
    		$this->form->bind($request->getParameter($this->form->getName()));
        	if ($this->form->isValid()) {
        		$this->form->save();
        		return $this->redirect('home#drev');
        	}
        }
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

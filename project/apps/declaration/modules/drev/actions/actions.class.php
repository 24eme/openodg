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

        return $this->redirect('drev_controle_externe', $this->drev);
    }

    public function executeControleExterne(sfWebRequest $request) {
        $this->drev = $this->getRoute()->getDRev();
    }

    public function executeValidation(sfWebRequest $request) {
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

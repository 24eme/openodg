<?php

class avaActions extends sfActions
{

    public function executeHome(sfWebRequest $request)
    {
		$this->etablissement = $this->getUser()->getEtablissement();
		
		$this->form = new EtablissementConfirmationEmailForm($this->etablissement);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {
            
            return sfView::SUCCESS;
        }
        
		$this->form->save();
		
        return $this->redirect('home');
    }

    
}

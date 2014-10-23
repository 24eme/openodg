<?php

class avaActions extends sfActions {

    public function executeHome(sfWebRequest $request) {

        if(null !== sfConfig::get('app_date_ouverture_drev')){
            $this->date_ouverture_drev = sfConfig::get('app_date_ouverture_drev');
            if(str_replace('-','',$this->date_ouverture_drev) >= date('Ymd')){
                return $this->setTemplate('ouverture');
            }
        }
        
        
        $this->etablissement = $this->getUser()->getEtablissement();

        $this->form = new EtablissementConfirmationEmailForm($this->etablissement);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect('home');
    }

}

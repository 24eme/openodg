<?php

class avaActions extends sfActions {

    public function executeHome(sfWebRequest $request) {
        $this->date_ouverture_drev = sfConfig::get('app_date_ouverture_drev');
        $this->date_ouverture_drevmarc = sfConfig::get('app_date_ouverture_drevmarc');
        
        $this->drev_non_ouverte = false;
        $this->drevmarc_non_ouverte = false;
        
        if (null !== $this->date_ouverture_drev) {
            if (str_replace('-', '', $this->date_ouverture_drev) >= date('Ymd')) {
                $this->drev_non_ouverte = true;
            }
        }
        
        if (null !== $this->date_ouverture_drevmarc) {
            if (str_replace('-', '', $this->date_ouverture_drevmarc) >= date('Ymd')) {
                $this->drevmarc_non_ouverte = true;
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

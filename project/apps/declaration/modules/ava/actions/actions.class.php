<?php

class avaActions extends sfActions {

    public function executeHome(sfWebRequest $request) {
        $this->date_ouverture_drev = sfConfig::get('app_date_ouvertures_drev');
        $this->date_ouverture_drevmarc = sfConfig::get('app_date_ouvertures_drevmarc');
        
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

        if(!$this->etablissement && $this->getUser()->hasCredential(myUser::CREDENTIAL_ADMIN)) {

            return $this->redirect('admin');
        } if(!$this->etablissement && $this->getUser()->hasCredential(myUser::CREDENTIAL_CONTACT)) {

            return $this->redirect('compte_recherche');
        } elseif(!$this->etablissement) {

            return $this->forwardSecure();
        }

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
    
	public function executeContact(sfWebRequest $request) {

        
    }
    
	public function executeMentionsLegales(sfWebRequest $request) {

        
    }

    protected function forwardSecure() {
        $this->context->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

        throw new sfStopException();
    }

}

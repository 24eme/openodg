<?php

class commonActions extends sfActions {

    public function executeAccueil(sfWebRequest $request) {
        if ($this->getUser()->hasCredential(myUser::CREDENTIAL_ADMIN)) {

            return $this->redirect('compte_search');
        }

        if ($this->getUser()->hasCredential(myUser::CREDENTIAL_CONTACT)) {

            return $this->redirect('compte_search');
        }

        if(!$this->getUser()->getEtablissement()) {

            return $this->forwardSecure();
        }

        if($this->getUser()->getEtablissement()->needEmailConfirmation()) {

            return $this->redirect('compte_teledeclarant_premiere_connexion');
        }

        return $this->redirect('compte_search', $this->getUser()->getEtablissement());
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

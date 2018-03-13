<?php

class parcellaireActions extends sfActions {
    public function executeIndex(sfWebRequest $request)
    {
        $this->form = new EtablissementChoiceForm('INTERPRO-declaration', array(), true);

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        return $this->redirect('parcellaire_declarant', $this->form->getValue('etablissement'));
    }


    public function executeEtablissementSelection(sfWebRequest $request) {
        $form = new EtablissementChoiceForm('INTERPRO-declaration', array(), true);
        $form->bind($request->getParameter($form->getName()));
        if (!$form->isValid()) {

            return $this->redirect('parcellaire');
        }

        return $this->redirect('parcellaire_declarant', $form->getEtablissement());
    }

    public function executeDeclarant(sfWebRequest $request) {
          $this->etablissement = $this->getRoute()->getEtablissement();
          $this->parcellaire = ParcellaireClient::getInstance()->getLast($this->etablissement->identifiant);

          $this->form = new EtablissementChoiceForm('INTERPRO-declaration', array(), true);

          $this->setTemplate('parcellaire');
    }

    public function executeVisualisation(sfWebRequest $request) {
        $this->parcellaire = $this->getRoute()->getParcellaire();
        $this->etablissement = $this->getRoute()->getEtablissement();
        $this->secureEtablissement($this->etablissement);
        $this->setTemplate('parcellaire');
    }

    protected function secureEtablissement($etablissement) {
        if (!EtablissementSecurity::getInstance($this->getUser(), $etablissement)->isAuthorized(array())) {

            return $this->forwardSecure();
        }
    }

    protected function forwardSecure() {
        $this->context->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

        throw new sfStopException();
    }
}

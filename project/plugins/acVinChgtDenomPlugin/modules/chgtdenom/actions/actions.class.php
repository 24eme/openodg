<?php

class chgtdenomActions extends sfActions {


    public function executeCreate(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();
        $this->secureEtablissement(EtablissementSecurity::DECLARANT_DREV, $etablissement);

        $chgtDenom = ChgtDenomClient::getInstance()->createDoc($etablissement->identifiant);
        $chgtDenom->save();

        return $this->redirect('chgtdenom_lots', $chgtDenom);
    }

    public function executeCreatePapier(sfWebRequest $request) {
        $etablissement = $this->getRoute()->getEtablissement();
        $this->secureEtablissement(EtablissementSecurity::DECLARANT_DREV, $etablissement);

        $chgtDenom = ChgtDenomClient::getInstance()->createDoc($etablissement->identifiant, null, true);
        $chgtDenom->save();

        return $this->redirect('chgtdenom_lots', $chgtDenom);
    }

    public function executeLots(sfWebRequest $request) {
        $this->chgtDenom = $this->getRoute()->getChgtDenom();
        $this->lots = $this->chgtDenom->getMvtLots();
    }

    public function executeEdition(sfWebRequest $request) {
        $this->chgtDenom = $this->getRoute()->getChgtDenom();
        $this->key = $request->getParameter("key", null);
        if (!$this->key && count($this->chgtDenom->lots) > 0) {
            $this->key = $this->chgtDenom->lots->get(0)->getGeneratedMvtKey();
        }
        $this->forward404Unless($this->key);

        if (!$this->chgtDenom->setLotFromMvtKey($this->key)) {
            throw new sfException("Lot inexistant pour la key : $this->key");
        }

        $this->form = new ChgtDenomForm($this->chgtDenom->lots->get(0));

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if (!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        $this->form->save();

        return $this->redirect('chgtdenom_validation', $this->chgtDenom);
    }

    public function executeValidation(sfWebRequest $request) {
        $this->chgtDenom = $this->getRoute()->getChgtDenom();
    }

    protected function secureEtablissement($droits, $etablissement) {
        if (!EtablissementSecurity::getInstance($this->getUser(), $etablissement)->isAuthorized($droits)) {

            return $this->forwardSecure();
        }
    }

    protected function forwardSecure() {
        $this->context->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

        throw new sfStopException();
    }

}

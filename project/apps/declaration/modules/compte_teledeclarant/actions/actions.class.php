<?php

class compte_teledeclarantActions extends sfActions {

    public function executePremiereConnexion(sfWebRequest $request) {
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

        $this->redirect('declaration_etablissement', $this->etablissement);
    }

}

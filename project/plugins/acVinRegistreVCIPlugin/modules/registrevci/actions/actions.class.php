<?php

class registreVCIActions extends sfActions {

  public function executeVisualisation(sfWebRequest $request) {
    $this->registre = $this->getRoute()->getRegistreVCI();
    $this->forward404Unless($this->registre);
  }

  public function executeAjoutMouvement(sfWebRequest $request) {
    $registreId = $request->getParameter('id');
    $this->registre = RegistreVCIClient::getInstance()->find($registreId);
    $this->form = new RegistreVCIAjoutMouvementForm($this->registre);

    if (!$request->isMethod(sfWebRequest::POST)) {
        return sfView::SUCCESS;
    }

    $this->form->bind($request->getParameter($this->form->getName()));

    if (!$this->form->isValid()) {
        return sfView::SUCCESS;
    }

    $this->form->save();
    return $this->redirect('registrevci_visualisation', array('id' => $this->registre->_id));
  }
}

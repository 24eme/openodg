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
  }
}

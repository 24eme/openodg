<?php

class registreVCIActions extends sfActions {

  public function executeVisualisation(sfWebRequest $request) {
    $this->registre = $this->getRoute()->getRegistreVCI();
    $this->forward404Unless($this->registre);
  }

}

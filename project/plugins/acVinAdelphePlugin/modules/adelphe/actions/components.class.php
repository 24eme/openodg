<?php

class adelpheComponents extends sfComponents {

  public function executeMonEspace(sfWebRequest $request) {
    $this->adelphe = AdelpheClient::getInstance()->findMasterByIdentifiantAndPeriode($this->etablissement->identifiant, $this->periode);
  }

}

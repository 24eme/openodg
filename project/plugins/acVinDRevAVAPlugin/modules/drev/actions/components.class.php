<?php

class drevComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->periode = substr($this->campagne, 0, 5);
        $this->drev = DRevClient::getInstance()->findMasterByIdentifiantAndCampagne($this->etablissement->identifiant, $this->campagne);
    }

    public function executeStepRevendication(sfWebRequest $request) {
        $this->ajoutForm = new DRevAjoutAppellationForm($this->drev);
    }

}

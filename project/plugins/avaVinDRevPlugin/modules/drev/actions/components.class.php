<?php

class drevComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->drev = DRevClient::getInstance()->findMasterByIdentifiantAndCampagne($this->etablissement->identifiant, $this->campagne);
    }

    public function executeStepRevendication(sfWebRequest $request) {
        $this->ajoutForm = new DRevAjoutAppellationForm($this->drev);
    }

}

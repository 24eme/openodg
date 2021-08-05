<?php

class drevComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        if (!$this->periode) {
            $this->periode = substr($this->campagne, 0, 4);
        }
        $this->campagne = $this->periode.'-'.($this->periode + 1);
        $this->drev = DRevClient::getInstance()->findMasterByIdentifiantAndCampagne($this->etablissement->identifiant, $this->periode);
    }

    public function executeStepRevendication(sfWebRequest $request) {
        $this->ajoutForm = new DRevAjoutAppellationForm($this->drev);
    }

}

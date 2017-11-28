<?php

class habilitationComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->habilitation = HabilitationClient::getInstance()->findMasterByIdentifiantAndCampagne($this->etablissement->identifiant, $this->campagne);

        $this->habilitationsHistory = HabilitationClient::getInstance()->getHistory($this->etablissement->identifiant);
    }

    public function executeStepRevendication(sfWebRequest $request) {
        $this->ajoutForm = new HabilitationAjoutAppellationForm($this->habilitation);
    }

}

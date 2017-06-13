<?php

class parcellaireCremantComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $campagne = ConfigurationClient::getInstance()->getCampagneManager()->getCurrentNext();
        $this->parcellaireCremant = ParcellaireClient::getInstance()->find('PARCELLAIRECREMANT-' . $this->etablissement->cvi . '-' . $campagne);
        $this->parcellairesCremantHistory = ParcellaireClient::getInstance()->getHistory($this->etablissement->identifiant,true);
    }

}

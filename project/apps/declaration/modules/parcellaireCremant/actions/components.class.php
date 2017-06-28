<?php

class parcellaireCremantComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->parcellaireCremant = ParcellaireClient::getInstance()->find('PARCELLAIRECREMANT-' . $this->etablissement->cvi . '-' . $this->campagne);
        $this->parcellairesCremantHistory = ParcellaireClient::getInstance()->getHistory($this->etablissement->identifiant,true);
    }

}

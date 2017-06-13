<?php

class parcellaireComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $campagne = ConfigurationClient::getInstance()->getCampagneManager()->getCurrentNext();
        $this->parcellaire = ParcellaireClient::getInstance()->find('PARCELLAIRE-' . $this->etablissement->cvi . '-' . $campagne);
        $this->parcellairesHistory = ParcellaireClient::getInstance()->getHistory($this->etablissement->identifiant);
    }

}

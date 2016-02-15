<?php

class parcellaireComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->etablissement = $this->getUser()->getEtablissement();
        $campagne = ConfigurationClient::getInstance()->getCampagneManager()->getCurrentNext();
        $this->parcellaire = ParcellaireClient::getInstance()->find('PARCELLAIRE-' . $this->etablissement->cvi . '-' . $campagne);
        $this->parcellairesHistory = ParcellaireClient::getInstance()->getHistory($this->etablissement->identifiant);
    }

}

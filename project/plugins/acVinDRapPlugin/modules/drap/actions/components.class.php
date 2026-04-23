<?php

class drapComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->parcellaire = DRapClient::getInstance()->getLast($this->etablissement->identifiant, acCouchdbClient::HYDRATE_JSON);
        $this->parcellaireIrrigable = DRapClient::getInstance()->find('PARCELLAIREIRRIGABLE-' . $this->etablissement->identifiant . '-' . $this->periode);
    }

}

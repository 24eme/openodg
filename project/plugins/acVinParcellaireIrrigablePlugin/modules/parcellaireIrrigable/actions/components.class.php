<?php

class parcellaireIrrigableComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->parcellaire = ParcellaireClient::getInstance()->getLast($this->etablissement->identifiant, acCouchdbClient::HYDRATE_JSON);
        $this->parcellaireIrrigable = ParcellaireIrrigableClient::getInstance()->find('PARCELLAIREIRRIGABLE-' . $this->etablissement->identifiant . '-' . $this->periode);
    }

}

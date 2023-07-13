<?php

class parcellaireManquantComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->parcellaire = ParcellaireClient::getInstance()->getLast($this->etablissement->identifiant, acCouchdbClient::HYDRATE_JSON);
        $this->parcellaireManquant = ParcellaireManquantClient::getInstance()->find('PARCELLAIREMANQUANT-' . $this->etablissement->identifiant . '-' . $this->periode);
    }

}

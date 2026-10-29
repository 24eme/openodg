<?php

class parcellaireManquantComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        if(strpos($this->etablissement->famille, 'PRODUCTEUR') === false) {
            return;
        }
        $this->parcellaire = ParcellaireClient::getInstance()->getLast($this->etablissement->identifiant, acCouchdbClient::HYDRATE_JSON);
        $this->parcellaireManquant = ParcellaireManquantClient::getInstance()->find('PARCELLAIREMANQUANT-' . $this->etablissement->identifiant . '-' . $this->periode);
        $this->needAffectation = ParcellaireAffectationClient::getInstance()->needAffectation($this->etablissement->identifiant, $this->periode);
    }

}

<?php

class parcellaireAffectationComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->campagne = "".($this->campagne+1);
        $this->parcellaireIrrigable = ParcellaireIrrigableClient::getInstance()->getLast($this->etablissement->identifiant, $this->campagne);
        $this->parcellaireAffectation = ParcellaireAffectationClient::getInstance()->getLast($this->etablissement->identifiant, $this->campagne);
    }

}

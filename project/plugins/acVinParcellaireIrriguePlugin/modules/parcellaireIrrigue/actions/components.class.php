<?php

class parcellaireIrrigueComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->campagne = "".($this->campagne+1);
        $this->parcellaireIrrigable = ParcellaireIrrigableClient::getInstance()->getLast($this->etablissement->identifiant, $this->campagne);
        $this->parcellaireIrrigue = ParcellaireIrrigueClient::getInstance()->getLast($this->etablissement->identifiant, $this->campagne);
    }

}

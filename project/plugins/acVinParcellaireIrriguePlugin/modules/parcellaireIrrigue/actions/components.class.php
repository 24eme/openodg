<?php

class parcellaireIrrigueComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->parcellaireIrrigable = ParcellaireIrrigableClient::getInstance()->getLast($this->etablissement->identifiant, $this->periode + 1);
        $this->parcellaireIrrigue = ParcellaireIrrigueClient::getInstance()->getLast($this->etablissement->identifiant, $this->periode + 1);
        $this->campagne = sprintf("%d-%d", $this->periode, $this->periode + 1);
    }

}

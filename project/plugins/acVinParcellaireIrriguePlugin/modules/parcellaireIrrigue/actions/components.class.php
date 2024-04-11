<?php

class parcellaireIrrigueComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->parcellaireIrrigable = ParcellaireIrrigableClient::getInstance()->findOrCreate($this->etablissement->identifiant, $this->periode);
        if(!$this->parcellaireIrrigable->_rev) {
            $this->parcellaireIrrigable = null;
        }
        $this->parcellaireIrrigue = ParcellaireIrrigueClient::getInstance()->getLast($this->etablissement->identifiant, $this->periode);
        if(!$this->parcellaireIrrigable) {
            $this->parcellaireIrrigue = null;
        }
        $this->campagne = sprintf("%d-%d", $this->periode, $this->periode + 1);
    }

}

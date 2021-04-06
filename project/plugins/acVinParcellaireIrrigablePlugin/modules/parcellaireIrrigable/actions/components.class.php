<?php

class parcellaireIrrigableComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->parcellaireIrrigable = ParcellaireIrrigableClient::getInstance()->find('PARCELLAIREIRRIGABLE-' . $this->etablissement->identifiant . '-' . $this->periode);
    }

}

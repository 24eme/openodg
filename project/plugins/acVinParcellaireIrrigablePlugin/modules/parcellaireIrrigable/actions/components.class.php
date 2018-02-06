<?php

class parcellaireIrrigableComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->parcellaireIrrigable = parcellaireIrrigableClient::getInstance()->find('PARCELLAIREIRRIGABLE-' . $this->etablissement->identifiant . '-' . $this->campagne);
    }

}

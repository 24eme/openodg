<?php

class parcellaireComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->parcellaire = ParcellaireClient::getInstance()->find('PARCELLAIRE-' . $this->etablissement->cvi . '-' . $this->campagne);
    }

}

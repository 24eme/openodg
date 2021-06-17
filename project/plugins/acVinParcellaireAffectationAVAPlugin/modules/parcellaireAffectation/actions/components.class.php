<?php

class parcellaireAffectationComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->parcellaire = ParcellaireAffectationClient::getInstance()->find('PARCELLAIRE-' . $this->etablissement->cvi . '-' . $this->campagne);
    }

}

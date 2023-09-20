<?php

class parcellaireIntentionAffectationComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->intentionParcellaireAffectation = ParcellaireIntentionClient::getInstance()->getLast($this->etablissement->identifiant);
    }

}

<?php

class parcellaireIntentionAffectationComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->intentionParcellaireAffectation = ParcellaireIntentionAffectationClient::getInstance()->getLast($this->etablissement->identifiant);
    }

}

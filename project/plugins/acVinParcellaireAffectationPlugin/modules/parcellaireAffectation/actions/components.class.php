<?php

class parcellaireAffectationComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->campagne = "".$this->campagne;
        $this->intentionParcellaireAffectation = ParcellaireIntentionAffectationClient::getInstance()->getLast($this->etablissement->identifiant);
        $this->parcellaireAffectation = ParcellaireAffectationClient::getInstance()->find('PARCELLAIREAFFECTATION-' . $this->etablissement->identifiant . '-' . $this->campagne);
    }
}

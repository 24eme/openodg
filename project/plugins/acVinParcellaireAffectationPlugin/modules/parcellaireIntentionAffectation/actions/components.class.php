<?php

class parcellaireIntentionAffectationComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->campagne = "".$this->campagne;
        $this->parcellaireAffectation = ParcellaireIntentionAffectationClient::getInstance()->find('PARCELLAIREINTENTIONAFFECTATION-' . $this->etablissement->identifiant . '-' . $this->campagne);
    }

}

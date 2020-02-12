<?php

class parcellaireAffectationComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->campagne = "".$this->campagne;
        $this->parcellaireAffectation = ParcellaireAffectationClient::getInstance()->find('PARCELLAIREAFFECTATION-' . $this->etablissement->identifiant . '-' . $this->campagne);
    }

}

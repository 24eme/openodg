<?php

class parcellaireAffectationComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->campagne = "".$this->campagne;
        $this->intentionParcellaireAffectation = ParcellaireIntentionAffectationClient::getInstance()->getLast($this->etablissement->identifiant);
        if (!$this->intentionParcellaireAffectation) {
            $this->intentionParcellaireAffectation = ParcellaireIntentionAffectationClient::getInstance()->createDoc($this->etablissement->identifiant, $this->campagne);
            if (!count($this->intentionParcellaireAffectation->declaration)) {
                $this->intentionParcellaireAffectation = null;
            }
        }
        $this->parcellaireAffectation = ParcellaireAffectationClient::getInstance()->find('PARCELLAIREAFFECTATION-' . $this->etablissement->identifiant . '-' . $this->periode);
    }
}

<?php

class parcellaireAffectationComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        if(strpos($this->etablissement->famille, 'PRODUCTEUR') === false) {
            return;
        }
        $this->intentionParcellaireAffectation = ParcellaireIntentionClient::getInstance()->getLastRealIntention($this->etablissement->identifiant);
        if (!$this->intentionParcellaireAffectation && ParcellaireConfiguration::getInstance()->affectationNeedsIntention()) {
            $this->intentionParcellaireAffectation = ParcellaireIntentionClient::getInstance()->createDoc($this->etablissement->identifiant, $this->periode);
            if (!count($this->intentionParcellaireAffectation->declaration)) {
                $this->intentionParcellaireAffectation = null;
            }
        }
        $this->parcellaireAffectation = ParcellaireAffectationClient::getInstance()->find('PARCELLAIREAFFECTATION-' . $this->etablissement->identifiant . '-' . $this->periode, acCouchdbClient::HYDRATE_JSON);
    }
}

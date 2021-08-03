<?php

class parcellaireAffectationCremantComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
    $this->parcellaireCremant = ParcellaireAffectationClient::getInstance()->find(ParcellaireAffectationClient::TYPE_COUCHDB_PARCELLAIRE_CREMANT.'-' . $this->etablissement->cvi . '-' . $this->periode);
    }

}

<?php

class parcellaireAffectationComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->parcellaire = ParcellaireAffectationClient::getInstance()->find(ParcellaireAffectationClient::TYPE_COUCHDB.'-' . $this->etablissement->cvi . '-' . $this->periode);
    }

}

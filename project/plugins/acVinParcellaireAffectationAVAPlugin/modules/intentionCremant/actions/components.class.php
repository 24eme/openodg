<?php

class intentionCremantComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->intentionCremant = ParcellaireAffectationClient::getInstance()->find(ParcellaireAffectationClient::TYPE_COUCHDB_INTENTION_CREMANT.'-' . $this->etablissement->cvi . '-' . $this->periode);
    }

}

<?php

class intentionCremantComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->intentionCremant = ParcellaireClient::getInstance()->find(ParcellaireClient::TYPE_COUCHDB_INTENTION_CREMANT.'-' . $this->etablissement->cvi . '-' . $this->campagne);
    }

}

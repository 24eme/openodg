<?php

class travauxmarcComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->travauxmarc = TravauxMarcClient::getInstance()->find(TravauxMarcClient::TYPE_COUCHDB.'-' . $this->etablissement->identifiant . '-' . $this->periode);
    }

}

<?php

class travauxmarcComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        if(!$this->periode) {
            $this->periode = $this->campagne;
        }
        $this->travauxmarc = TravauxMarcClient::getInstance()->find(TravauxMarcClient::TYPE_COUCHDB.'-' . $this->etablissement->identifiant . '-' . $this->periode);
    }

}

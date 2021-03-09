<?php

class conditionnementComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->conditionnement = ConditionnementClient::getInstance()->findByIdentifiantAndDate($this->etablissement->identifiant, date('Ymd'));
    }

}

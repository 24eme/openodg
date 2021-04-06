<?php

class conditionnementComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->conditionnement = ConditionnementClient::getInstance()->findBrouillon($this->etablissement->identifiant);
    }

}

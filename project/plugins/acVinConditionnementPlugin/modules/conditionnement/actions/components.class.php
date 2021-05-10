<?php

class conditionnementComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->conditionnement = ConditionnementClient::getInstance()->findBrouillon($this->etablissement->identifiant);
        if (!$this->conditionnement) {
            $this->conditionnement = ConditionnementClient::getInstance()->findByIdentifiantAndDate($this->etablissement->identifiant, date('Y-m-d'));
        }
    }

}

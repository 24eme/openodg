<?php

class fichierComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
    	$this->dr = DRClient::getInstance()->findByArgs($this->etablissement->identifiant, $this->campagne);   
    }

}

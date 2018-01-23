<?php

class fichierComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        if(class_exists("DRClient")) {
    	    $this->dr = DRClient::getInstance()->findByArgs($this->etablissement->identifiant, $this->campagne);   
        }
    }

}

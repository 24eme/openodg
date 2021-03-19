<?php

class fichierComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        if(class_exists("DRClient")) {
    	    $this->dr = DRClient::getInstance()->findByArgs($this->etablissement->identifiant, $this->periode);
        }
        if(!$this->dr && class_exists("SV11Client")) {
    	    $this->sv = SV11Client::getInstance()->findByArgs($this->etablissement->identifiant, $this->periode);
        }
        if(!$this->sv && class_exists("SV12Client")) {
    	    $this->sv = SV12Client::getInstance()->findByArgs($this->etablissement->identifiant, $this->periode);
        }
    }

}

<?php

class tirageComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->etablissement = $this->getUser()->getEtablissement();
        $campagne = ConfigurationClient::getInstance()->getCampagneManager()->getCurrent();
        $this->drev = null;
        $this->drevsHistory = array();
    }

}

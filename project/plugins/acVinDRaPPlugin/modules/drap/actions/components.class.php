<?php

class drapComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->parcellaire = DRaPClient::getInstance()->getLast($this->etablissement->identifiant, acCouchdbClient::HYDRATE_JSON);

        if (!$this->parcellaire) {
            $this->parcellaire = DRaPClient::getInstance()->createDoc($this->etablissement->identifiant, $this->periode);
        }

        $this->drap = DRaPClient::getInstance()->find('DRAP-' . $this->etablissement->identifiant . '-' . $this->periode);
    }

}

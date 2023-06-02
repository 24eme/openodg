<?php

class pmcComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $campagne = $request->getParameter('campagne', ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_COMPLET)->getCurrent());
        $date = date('Y-m-d');
        if ($campagne != ConfigurationClient::getInstance()->getCampagneVinicole()->getCampagneByDate($date)) {
            $date = substr($request->getParameter('campagne'), 5, 4).'-07-31';
        }

        $this->pmc = PMCClient::getInstance()->findBrouillon($this->etablissement->identifiant, $campagne);
        if (!$this->pmc) {
            $this->pmc = PMCClient::getInstance()->findByIdentifiantAndDate($this->etablissement->identifiant, $date);
        }
    }

}

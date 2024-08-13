<?php

class pmcComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $periode = ($this->periode) ? : $request->getParameter('periode', ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_COMPLET)->getCurrent());
        $date = date('Y-m-d');
        if (ConfigurationClient::getInstance()->buildCampagneFromYearOrCampagne($periode) != ConfigurationClient::getInstance()->getCampagneVinicole()->getCampagneByDate($date)) {
            $date = substr($request->getParameter('periode'), 5, 4).'-07-31';
        }

        $this->pmc = PMCClient::getInstance()->findBrouillon($this->etablissement->identifiant, $periode);
        if (!$this->pmc) {
            $this->pmc = PMCClient::getInstance()->findByIdentifiantAndDate($this->etablissement->identifiant, $date);
        }
    }

}

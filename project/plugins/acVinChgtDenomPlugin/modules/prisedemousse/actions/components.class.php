<?php

class prisedemousseComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $defaultCampagne = ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_COMPLET)->getCurrent();
        $this->campagne = $request->getParameter('campagne',$defaultCampagne);

        $this->enCours = null;
        $pdms = PriseDeMousseClient::getInstance()->getHistory($this->etablissement->identifiant);

        foreach($pdms as $pdm) {
            if (!$pdm->isValide() && $pdm->campagne == $this->campagne) {
                $this->enCours = $pdm;
                break;
            }
        }
    }

}

<?php

class conditionnementComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->date_ouverture_conditionnement = sfConfig::get('app_date_ouverture_conditionnement');
        $this->conditionnement_non_ouverte = false;
        if (null !== $this->date_ouverture_conditionnement) {
            if (str_replace('-', '', $this->date_ouverture_conditionnement) > date('Ymd')) {
                $this->conditionnement_non_ouverte = true;
            }
        }
        $this->conditionnement = ConditionnementClient::getInstance()->findMasterByIdentifiantAndCampagne($this->etablissement->identifiant, $this->campagne);

        $this->conditionnementsHistory = ConditionnementClient::getInstance()->getHistory($this->etablissement->identifiant);
    }

}

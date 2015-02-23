<?php

class parcellaireCremantComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->date_ouverture_parcellaire_cremant = sfConfig::get('app_date_ouverture_parcellaire_cremant');
        $this->parcellaire_cremant_non_ouverte = false;
        if (null !== $this->date_ouverture_parcellaire_cremant) {
            if (str_replace('-', '', $this->date_ouverture_parcellaire_cremant) > date('Ymd')) {
                $this->parcellaire_cremant_non_ouverte = true;
            }
        }
        $this->etablissement = $this->getUser()->getEtablissement();
        $campagne = ConfigurationClient::getInstance()->getCampagneManager()->getCurrentNext();
        $this->parcellaireCremant = ParcellaireClient::getInstance()->find('PARCELLAIRECEMANT-' . $this->etablissement->cvi . '-' . $campagne);
        $this->parcellairesCremantHistory = ParcellaireClient::getInstance()->getHistory($this->etablissement->identifiant,true);
    }

}

<?php

class parcellaireComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->date_ouverture_parcellaire = sfConfig::get('app_date_ouverture_parcellaire');
        $this->parcellaire_non_ouverte = false;
        if (null !== $this->date_ouverture_parcellaire) {
            if (str_replace('-', '', $this->date_ouverture_parcellaire) > date('Ymd')) {
                $this->parcellaire_non_ouverte = true;
            }
        }
        $this->etablissement = $this->getUser()->getEtablissement();
        $campagne = ConfigurationClient::getInstance()->getCampagneManager()->getCurrent();
        $this->parcellaire = ParcellaireClient::getInstance()->find('PARCELLAIRE-' . $this->etablissement->cvi . '-' . $campagne);
        $this->parcellairesHistory = ParcellaireClient::getInstance()->getHistory($this->etablissement->identifiant);
    }

}

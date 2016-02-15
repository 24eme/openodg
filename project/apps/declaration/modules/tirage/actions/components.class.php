<?php

class tirageComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->date_ouverture_tirage = sfConfig::get('app_date_ouverture_tirage');
        $this->tirage_non_ouverte = false;
        if (null !== $this->date_ouverture_drev) {
            if (str_replace('-', '', $this->date_ouverture_tirage) > date('Ymd')) {
                $this->tirage_non_ouverte = true;
            }
        }
        $this->etablissement = $this->getUser()->getEtablissement();
        $campagne = ConfigurationClient::getInstance()->getCampagneManager()->getCurrent();
        $this->drev = null;
        $this->drevsHistory = array();
    }

}

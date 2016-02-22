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

        $this->nbDeclaration = TirageClient::getInstance()->getLastNumero($this->etablissement->identifiant, $campagne);
        $nextNumero = $this->nbDeclaration + 1;

        $this->tirage = TirageClient::getInstance()->find('TIRAGE-' . $this->etablissement->identifiant . '-' . $campagne. sprintf("%02d", $nextNumero));
        $this->tiragesHistory = array();
        $this->nieme = '';
        if ($nextNumero > 1) {
            $this->nieme = $nextNumero."ème";
        }
    }

}

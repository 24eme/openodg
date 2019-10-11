<?php

class drevComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->date_ouverture_drev = sfConfig::get('app_date_ouverture_drev');
        $this->drev_non_ouverte = false;
        if (null !== $this->date_ouverture_drev) {
            if (str_replace('-', '', $this->date_ouverture_drev) > date('Ymd')) {
                $this->drev_non_ouverte = true;
            }
        }
        $this->drev = DRevClient::getInstance()->findMasterByIdentifiantAndCampagne($this->etablissement->identifiant, $this->campagne);

        $this->drevsHistory = DRevClient::getInstance()->getHistory($this->etablissement->identifiant);
    }

    public function executeLegalSignature() {
        if (!$this->etablissement)
            throw new sfException('need an identifiant of etablissement ('.$this->etablissement.' provided)');
        $this->legalSignatureForm = new DRevLegalSignatureForm($this->etablissement);
    }

}

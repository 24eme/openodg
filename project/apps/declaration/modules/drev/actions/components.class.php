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
        $this->etablissement = $this->getUser()->getEtablissement();
        $campagne = ConfigurationClient::getInstance()->getCampagneManager()->getCurrent();
        $this->drev = DRevClient::getInstance()->find('DREV-' . $this->etablissement->identifiant . '-' . $campagne);
        $this->drevsHistory = DRevClient::getInstance()->getHistory($this->etablissement->identifiant);
    }

    public function executeStepRevendication(sfWebRequest $request) {
        $this->ajoutForm = new DRevAjoutAppellationForm($this->drev);
    }

}

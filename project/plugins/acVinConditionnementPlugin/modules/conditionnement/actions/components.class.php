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
        if ($this->conditionnement && $this->conditionnement->isAutoReouvrable()) {
          $this->conditionnement->devalidate();
          $this->conditionnement->etape = ConditionnementEtapes::ETAPE_LOTS;
          $this->conditionnement->save();
        } else {
          $this->transaction = null;
        }
    }

}

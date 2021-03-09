<?php

class conditionnementComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->conditionnement = ConditionnementClient::getInstance()->findByIdentifiantAndDate($this->etablissement->identifiant, date('Ymd'));
        if ($this->conditionnement && $this->conditionnement->isAutoReouvrable()) {
          $this->conditionnement->devalidate();
          $this->conditionnement->etape = ConditionnementEtapes::ETAPE_LOTS;
          $this->conditionnement->save();
        } else {
          $this->transaction = null;
        }
    }

}

<?php

class transactionComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->transaction = TransactionClient::getInstance()->findByIdentifiantAndDate($this->etablissement->identifiant, date('Ymd'));
        if ($this->transaction && $this->transaction->isAutoReouvrable()) {
          $this->transaction->devalidate();
          $this->transaction->etape = ConditionnementEtapes::ETAPE_LOTS;
          $this->transaction->save();
        } else {
          $this->transaction = null;
        }
    }

}

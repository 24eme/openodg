<?php

class transactionComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->date_ouverture_transaction = sfConfig::get('app_date_ouverture_transaction');
        $this->transaction_non_ouverte = false;
        if (null !== $this->date_ouverture_transaction) {
            if (str_replace('-', '', $this->date_ouverture_transaction) > date('Ymd')) {
                $this->transaction_non_ouverte = true;
            }
        }
        $this->transaction = TransactionClient::getInstance()->findMasterByIdentifiantAndCampagne($this->etablissement->identifiant, $this->campagne);
        $this->transactionsHistory = TransactionClient::getInstance()->getHistory($this->etablissement->identifiant);
    }

}

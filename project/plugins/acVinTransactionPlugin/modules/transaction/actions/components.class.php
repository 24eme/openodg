<?php

class transactionComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $this->transaction = TransactionClient::getInstance()->findByIdentifiantAndDate($this->etablissement->identifiant, date('Ymd'));
    }

}

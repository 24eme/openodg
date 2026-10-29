<?php

class transactionComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $date = date('Y-m-d');
        $this->transaction = TransactionClient::getInstance()->findByIdentifiantAndDate($this->etablissement->identifiant, $date);
    }

}

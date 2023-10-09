<?php

class transactionComponents extends sfComponents {

    public function executeMonEspace(sfWebRequest $request) {
        $campagne = $request->getParameter('campagne', $this->campagne);
        $date = date('Y-m-d');
        if ($campagne != ConfigurationClient::getInstance()->getCampagneVinicole()->getCampagneByDate($date)) {
            $date = substr($request->getParameter('campagne'), 5, 4).'-07-31';
        }

        $this->transaction = TransactionClient::getInstance()->findBrouillon($this->etablissement->identifiant, $campagne);
        if (!$this->transaction) {
            $this->transaction = TransactionClient::getInstance()->findByIdentifiantAndDate($this->etablissement->identifiant, $date);
        }

    }

}

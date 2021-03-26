<?php


class TransactionLot extends BaseTransactionLot
{
    public function getDocumentType() {

        return TransactionClient::TYPE_MODEL;
    }

    public function getDocumentOrdre() {

        return "01";
    }

    public function getMouvementFreeInstance() {

        return TransactionMouvementLots::freeInstance($this->getDocument());
    }
}

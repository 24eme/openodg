<?php


class TransactionLot extends BaseTransactionLot
{
    public function getInitialType() {
        if(is_null($this->_get('initial_type'))) {
            $this->initial_type = $this->getDocumentType();
        }

        return $this->_get('initial_type');
    }

    public function getDocumentType() {

        return TransactionClient::TYPE_MODEL;
    }

    public function getDocumentOrdre() {
        $this->_set('document_ordre', '01');
        return "01";
    }

    public function getMouvementFreeInstance() {

        return TransactionMouvementLots::freeInstance($this->getDocument());
    }
}

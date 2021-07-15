<?php


class TransactionLot extends BaseTransactionLot
{
    public function getOrigineType() {
        if(is_null($this->_get('origine_type'))) {
            $this->origine_type = $this->getDocumentType();
        }

        return $this->_get('origine_type');
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

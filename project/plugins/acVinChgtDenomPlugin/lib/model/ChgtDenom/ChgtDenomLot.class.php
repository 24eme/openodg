<?php

class ChgtDenomLot extends BaseChgtDenomLot
{
    public function getDocumentType() {

        return ChgtDenomClient::TYPE_MODEL;
    }

    public function getDocumentOrdre() {

        return $this->_get('document_ordre');
    }

    public function getLibelle() {

        return parent::getLibelle();
    }

    public function getMouvementFreeInstance() {

        return ChgtDenomMouvementLots::freeInstance($this->getDocument());
    }

    public function getLotProvenance()
    {
        return $this->getLotDocumentOrdre(intval($this->document_ordre) - 1, true);
    }

}

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

    public function isLogementEditable()
    {
        $chgt = $this->getDocument();

        if ($chgt->isValide()) {
            return false;
        }

        if (count($chgt->lots) === 1) {
            return true;
        }

        if ($this->numero_archive === $chgt->lots->get(1)->numero_archive) {
            return true;
        }

        return false;
    }
}

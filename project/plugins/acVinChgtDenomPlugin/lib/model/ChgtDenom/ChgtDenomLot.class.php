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

    public function isLogementEditable()
    {
        $chgt = $this->getDocument();

        if ($chgt->isValide()) {
            return false;
        }

        if ($chgt->changement_type === ChgtDenomClient::CHANGEMENT_TYPE_DECLASSEMENT) {
            if ($chgt->isTotal()) {
                return false;
            }

            if ($this->numero_archive === $chgt->lots->get(1)->numero_archive) {
                return false;
            }

            return true;
        }

        return true;
    }

    public function isLotOrigine()
    {
        $chgt = $this->getDocument();

        if ($chgt->isTotal()) {
            return false;
        }

        if ($this->numero_archive === $chgt->lots->get(1)->numero_archive) {
            return false;
        }

        return true;
    }

}

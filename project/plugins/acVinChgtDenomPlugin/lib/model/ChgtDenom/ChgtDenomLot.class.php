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
        if (!$this->volume) {
            return false;
        }

        return true;
    }

    public function isLotOrigine()
    {
        $chgt = $this->getDocument();

        if ($chgt->isTotal()) {
            return false;
        }

        if ($this->unique_id === $chgt->changement_origine_lot_unique_id) {
            return true;
        }

        return false;
    }

    public function getInitialType() {
        if(is_null($this->_get('initial_type'))) {
            $firstOrigineLot = $this->getDocument()->getFirstOrigineLot();
            $initialType = null;
            if($firstOrigineLot) {
                $initialType = $firstOrigineLot->getInitialType();
            } else {
                $initialType = $this->getDocument()->type;
            }

            if($this->produit_hash == $this->getDocument()->changement_produit_hash && strpos($initialType, LotsClient::INITIAL_TYPE_CHANGE) === false) {
                $initialType .= ":".LotsClient::INITIAL_TYPE_CHANGE;
            }
            $this->initial_type = $initialType;
        }

        return $this->_get('initial_type');
    }

}

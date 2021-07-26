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

    public function getOrigineType() {
        $origine = LotsClient::getInstance()->findByUniqueId($this->getDocument()->identifiant, $this->getDocument()->changement_origine_lot_unique_id, "01");
        if ($origine && $origine->getDocument()->_id != $this->getDocument()->_id) {
            $this->origine_type = $origine->origine_type;
        }else{
            $this->origine_type = $this->getDocument()->type;
        }
        return $this->_get('origine_type');
    }

}

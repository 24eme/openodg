<?php


class CourrierLot extends BaseCourrierLot
{
    public function getMouvementFreeInstance() {

        return CourrierMouvementLots::freeInstance($this->getDocument());
    }

    public function getDocumentType() {

        return CourrierClient::TYPE_MODEL;
    }

    public function isLogementEditable()
    {
        return false;
    }

    public function isLotOrigine()
    {
        return false;
    }


}

<?php


class CourrierLot extends BaseCourrierLot
{
    public function getMouvementFreeInstance() {

        return CourrierMouvementLots::freeInstance($this->getDocument());
    }

    public function getDocumentOrdre() {
        if ($this->id_document_provenance === null) {
            throw new sfException('Doit avoir un document provenance');
        }

        return $this->_get('document_ordre');
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

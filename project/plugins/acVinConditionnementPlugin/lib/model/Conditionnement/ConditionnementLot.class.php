<?php


class ConditionnementLot extends BaseConditionnementLot
{
    public function getFieldsToFill() {
        $fields = parent::getFieldsToFill();
        $fields[] = 'centilisation';
        return $fields;
    }

    public function setCentilisation($centilisation) {
        if (!$this->exist('centilisation')) {
            $this->add('centilisation');
        }
        return $this->_set('centilisation', $centilisation);
    }

    public function getCentilisation() {
        $c = null;
        if ($this->exist('centilisation')) {
            $c = $this->_get('centilisation');
        }
        return $c;
    }

    public function getDocumentType() {

        return ConditionnementClient::TYPE_MODEL;
    }

    public function getDocumentOrdre() {
        $this->_set('document_ordre', '01');
        return "01";
    }

    public function getMouvementFreeInstance() {

        return ConditionnementMouvementLots::freeInstance($this->getDocument());
    }

}

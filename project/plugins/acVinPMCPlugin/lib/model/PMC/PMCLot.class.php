<?php


class PMCLot extends BasePMCLot
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

    public function getIntialType() {
        if(is_null($this->_get('initial_type'))) {
            $this->initial_type = $this->getDocumentType();
        }

        return $this->_get('initial_type');
    }

    public function getDocumentType() {

        return PMCClient::TYPE_MODEL;
    }

    public function getDocumentOrdre() {
        $this->_set('document_ordre', '01');
        return "01";
    }

    public function getMouvementFreeInstance() {

        return PMCMouvementLots::freeInstance($this->getDocument());
    }

    public function getDateDegustationVoulueFr()
    {
        return Date::francizeDate($this->_get('date_degustation_voulue'));
    }

    public function getDateCommissionFr($format = 'd/m/Y')
    {
        return DateTimeImmutable::createFromFormat('Y-m-d', $this->_get('date_commission'))->format($format);
    }
}

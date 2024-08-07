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

    public function getInitialType() {
        $initial_type = '';
        if ($this->getDocument()->type == PMCClient::TYPE_MODEL) {
            $initial_type = PMCClient::TYPE_MODEL;
        }else{
            if ($this->id_document_provenance && $this->getLotProvenance() && isset($this->getLotProvenance()->initial_type)) {
                $initial_type =  $this->getLotProvenance()->initial_type.':'.LotsClient::INITIAL_TYPE_NC;
            }else{
                $initial_type = PMCNCClient::TYPE_MODEL;
            }
        }
        $this->_set('initial_type', $initial_type);
        return $initial_type;
    }

    public function getDocumentType() {

        return $this->getDocument()->getType();
    }

    public function getDocumentOrdre() {
        if ($this->getDocument()->getType() === PMCClient::TYPE_MODEL) {
            $this->_set('document_ordre', "01");
        } else {
            $this->_set('document_ordre', $this->getDocumentOrdreCalcule());
        }

        return $this->_get('document_ordre');
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

<?php


class ConditionnementLot extends BaseConditionnementLot
{
    public function getFieldsToFill() {
        return array('numero_logement_operateur', 'millesime', 'volume', 'produit_hash', 'destination_date', 'elevage', 'specificite', 'centilisation');
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

        return "01";
    }

    public function getMouvementFreeInstance() {

        return ConditionnementMouvementLots::freeInstance($this->getDocument());
    }

}

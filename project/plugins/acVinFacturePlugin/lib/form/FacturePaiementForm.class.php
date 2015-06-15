<?php

class FacturePaiementForm extends acCouchdbObjectForm {

    public function configure()
    {
        $this->setWidget('date_paiement', new sfWidgetFormInput(array(), array()));
        $this->setValidator('date_paiement', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));

        $this->setWidget('reglement_paiement', new sfWidgetFormInput());
        $this->setValidator('reglement_paiement', new sfValidatorString(array('required' => false)));

        $this->widgetSchema->setNameFormat('facture_paiement[%s]');
    }

    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }

}

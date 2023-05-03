<?php

class PMCLotForm extends TransactionLotForm
{
    public function configure() {
        parent::configure();

        $this->setWidget('elevage', new sfWidgetFormInputCheckbox());
        $this->setValidator('elevage', new sfValidatorBoolean(['required' => false]));

        unset($this['destination_date']);

        $this->widgetSchema->setNameFormat('[%s]');
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);

        if (!empty($values['elevage'])) {
          $this->getObject()->statut = Lot::STATUT_ELEVAGE;
        }
    }
}

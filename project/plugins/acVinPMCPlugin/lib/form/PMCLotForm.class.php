<?php

class PMCLotForm extends TransactionLotForm
{
    public function configure() {
        parent::configure();

        $this->setWidget('date_degustation_voulue', new sfWidgetFormInput(array(), array()));
        $this->setValidator('date_degustation_voulue', new sfValidatorDate(array('with_time' => false, 'datetime_output' => 'Y-m-d', 'date_format' => '~(?<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => false)));

        //unset($this['destination_date']);

        $this->widgetSchema->setNameFormat('[%s]');
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);

        if (!empty($values['date_degustation_voulue'])) {
          $this->getObject()->date_degustation_voulue = $values['date_degustation_voulue'];
        }
    }
}

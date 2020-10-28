<?php

class ChgtDenomValidationForm extends acCouchdbObjectForm
{

    public function configure() {
        $this->setWidget('validation', new sfWidgetFormInputHidden());
        $this->setValidator('validation', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<year>\d{4})-(?P<month>\d{2})-(?P<day>\d{2})~', 'required' => true)));
        $this->widgetSchema->setNameFormat('chgt_denom_validation[%s]');
    }

    protected function updateDefaultsFromObject() {
      parent::updateDefaultsFromObject();
      $defaults = $this->getDefaults();
      $defaults['validation'] = date('Y-m-d');
      $this->setDefaults($defaults);
    }
}

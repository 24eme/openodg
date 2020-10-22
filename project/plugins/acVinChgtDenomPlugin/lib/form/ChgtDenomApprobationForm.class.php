<?php

class ChgtDenomApprobationForm extends acCouchdbObjectForm
{

    public function configure() {
        $this->setWidget('validation_odg', new sfWidgetFormInputHidden());
        $this->setValidator('validation_odg', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<year>\d{4})-(?P<month>\d{2})-(?P<day>\d{2})~', 'required' => true)));
        $this->setWidget('deguster', new sfWidgetFormInputCheckbox());
        $this->setValidator('deguster', new sfValidatorBoolean(['required' => false]));
        $this->widgetSchema->setNameFormat('chgt_denom_approbation[%s]');
    }

    protected function updateDefaultsFromObject() {
      parent::updateDefaultsFromObject();
      $defaults = $this->getDefaults();
      $defaults['validation_odg'] = date('Y-m-d');
      $this->setDefaults($defaults);
    }

    public function doUpdateObject($values) {
        parent::doUpdateObject($values);
        if ($values['deguster']) {
          $this->getObject()->generateMouvementsLots();
        }
    }
}

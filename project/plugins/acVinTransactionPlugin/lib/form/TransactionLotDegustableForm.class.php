<?php
class TransactionLotDegustableForm extends acCouchdbObjectForm
{
    public function configure() {
        $this->setWidget('affectable', new sfWidgetFormInputCheckbox());
        $this->setValidator('affectable', new sfValidatorBoolean(['required' => false]));
        $this->widgetSchema->setNameFormat('[%s]');
    }
}

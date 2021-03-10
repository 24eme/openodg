<?php
class DRevLotDegustableForm extends acCouchdbObjectForm
{
    public function configure() {
        $this->setWidget('degustable', new sfWidgetFormInputCheckbox());
        $this->setValidator('degustable', new sfValidatorBoolean(['required' => false]));
        $this->widgetSchema->setNameFormat('[%s]');
    }
}

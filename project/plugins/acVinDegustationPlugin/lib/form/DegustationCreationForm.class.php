<?php

class DegustationCreationForm extends acCouchdbObjectForm
{
    public function configure() {
        $this->setWidget('date', new sfWidgetFormInput(array(), array()));
        $this->setValidator('date', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));
        $this->widgetSchema->setNameFormat('degustation_creation[%s]');
    }
}

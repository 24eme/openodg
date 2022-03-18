<?php

class DegustationCreationForm extends BaseForm
{
    public function configure() {
        $this->setWidget('date', new bsWidgetFormInput(array(), array()));
        $this->setValidator('date', new sfValidatorDate(array('datetime_output' => 'Y-m-d', 'date_format' => '~(?<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));

        $this->setWidget('time', new bsWidgetFormInput(array("type"=>'time'), array()));
        $this->setValidator('time', new sfValidatorTime(array('time_output' => 'H:i', 'time_format' => '~(?<hour>\d{2}):(?P<minute>\d{2})~', 'required' => true)));

        $this->setWidget('lieu', new bsWidgetFormChoice(array('choices' => $this->getLieuxChoices())));
        $this->setValidator('lieu', new sfValidatorPass(array('required' => true)));

        $this->setWidget('max_lots', new bsWidgetFormInput());
        $this->setValidator('max_lots', new sfValidatorNumber(array('required' => false)));

        $this->widgetSchema->setNameFormat('degustation_creation[%s]');
    }

    public static function getLieuxChoices() {
        $lieux = array(null=>null);
        return array_merge($lieux, DegustationConfiguration::getInstance()->getLieux());
    }

    public function save() {
        $values = $this->getValues();

        $degustation = DegustationClient::getInstance()->createDoc($values['date']." ".$values['time'].":00");
        $degustation->lieu = $values['lieu'];
        $degustation->max_lots = $values['max_lots'];
        $degustation->save();

        return $degustation;
    }
}

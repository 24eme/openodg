<?php

class TourneeCreationForm extends BaseForm
{
    public function configure() {
        $this->setWidget('date', new bsWidgetFormInputDate(array(), array()));
        $this->setValidator('date', new sfValidatorDate(array('datetime_output' => 'Y-m-d', 'date_format' => '~(?<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));

        $this->widgetSchema->setNameFormat('tournee_creation[%s]');
    }

    public function save() {
        $values = $this->getValues();
        $tournee = TourneeClient::getInstance()->createDoc($values['date']." 00:00:00", sfContext::getInstance()->getUser()->getRegion());
        $tournee->region = Organisme::getInstance()->getOIRegion();
        $tournee->save();

        return $tournee;
    }
}

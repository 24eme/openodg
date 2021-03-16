<?php

class DegustationCreationForm extends acCouchdbObjectForm
{
    public function configure() {
        $this->setWidget('date', new bsWidgetFormInput(array(), array()));
        $this->setValidator('date', new sfValidatorDate(array('datetime_output' => 'Y-m-d', 'date_format' => '~(?<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));

        $this->setWidget('time', new bsWidgetFormInput(array("type"=>'time'), array()));
        $this->setValidator('time', new sfValidatorTime(array('time_output' => 'H:i', 'time_format' => '~(?<hour>\d{2}):(?P<minute>\d{2})~', 'required' => true)));

        $this->setWidget('lieu', new bsWidgetFormChoice(array('choices' => $this->getLieuxChoices())));
        $this->setValidator('lieu', new sfValidatorChoice(array('choices' => array_keys($this->getLieuxChoices()), 'required' => true)));

        $this->setWidget('max_lots', new bsWidgetFormInput());
        $this->setValidator('max_lots', new sfValidatorNumber(array('required' => false)));

        $this->widgetSchema->setNameFormat('degustation_creation[%s]');
    }

    public static function getLieuxChoices() {
        $lieux = array(null=>null);
        $commisions = DegustationConfiguration::getInstance()->getCommissions();
        foreach ($commisions as $commission) {
            $lieux[$commission] = $commission;
        }
        return $lieux;
    }

    protected function doUpdateObject($values) {
		  parent::doUpdateObject($values);
      $dateVal = str_replace("-", "", preg_replace("/(.+)$/","$1",$values['date']));
      $timeVal = $values['time'];
      $dateTime = DateTime::createFromFormat('Ymd H:i',$dateVal." ".$timeVal);
      $this->getObject()->set('date', $dateTime->format("Y-m-d H:i"));
    }

    public function save($con = null) {
        $values = $this->getValues();
        $dateVal = str_replace("-", "", preg_replace("/(.+)$/","$1",$values['date']));
        $timeVal = $values['time'];
        $dateTime = DateTime::createFromFormat('Ymd H:i',$dateVal." ".$timeVal);
        $lieu = Degustation::getNomByLieu($values['lieu'], true);
        $degustation = DegustationClient::getInstance()->find(sprintf("%s-%s-%s", DegustationClient::TYPE_COUCHDB, $dateTime->format("YmdHi"), $lieu));
        if ($degustation) {
            return $degustation;
        } else {
            return parent::save($con);
        }

    }
}

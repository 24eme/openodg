<?php

class DegustationCreationForm extends acCouchdbObjectForm
{
    public function configure() {
        $this->setWidget('date', new sfWidgetFormInput(array(), array()));
        $this->setValidator('date', new sfValidatorDate(array('with_time' => true, 'datetime_output' => 'Y-m-d H:i', 'date_format' => '~(?<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4}) à (?P<hour>\d{2}):(?P<minute>\d{2})~', 'required' => true)));
        $this->setWidget('lieu', new sfWidgetFormChoice(array('choices' => $this->getLieuxChoices())));
        $this->setValidator('lieu', new sfValidatorChoice(array('choices' => array_keys($this->getLieuxChoices()), 'required' => true)));
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

    public function save($con = null) {
        $values = $this->getValues();
        $dateHeureMinute = str_replace("-", "", preg_replace("/(.+) (.+):(.+):(.+)$/","$1$2$3",$values['date']));
        $lieu = Degustation::getNomByLieu($values['lieu'], true);
        $degustation = DegustationClient::getInstance()->find(sprintf("%s-%s-%s", DegustationClient::TYPE_COUCHDB, $dateHeureMinute, $lieu));

        if ($degustation) {
            return $degustation;
        } else {
            return parent::save($con);
        }

    }
}

<?php

class DegustationCreationForm extends acCouchdbObjectForm
{
    public function configure() {
        $this->setWidget('date', new sfWidgetFormInput(array(), array()));
        $this->setValidator('date', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));
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
        $date = str_replace("-", "", $values['date']);
        $lieu = Degustation::getNomByLieu($values['lieu'], true);
        $degustation = DegustationClient::getInstance()->find(sprintf("%s-%s-%s", DegustationClient::TYPE_COUCHDB, $date, $lieu));
        if ($degustation) {
            return $degustation;
        } else {
            return parent::save($con);
        }
        
    }
}

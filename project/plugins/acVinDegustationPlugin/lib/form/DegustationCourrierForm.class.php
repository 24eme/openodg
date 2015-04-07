<?php

class DegustationCourrierForm extends acCouchdbObjectForm {

    public function __construct(\acCouchdbJson $object, $options = array(), $CSRFSecret = null) {
        parent::__construct($object, $options, $CSRFSecret);
        $this->updateDefaults();
    }

    public function configure() {
        foreach ($this->getObject()->getNotes() as $note) {
            $this->setWidget($note->prelevement->getHashForKey(), new sfWidgetFormChoice(array('choices' => $this->getTypesCourrier())));
            $this->setValidator($note->prelevement->getHashForKey(), new sfValidatorChoice(array('choices' => array_keys($this->getTypesCourrier()), 'required' => false)));
            
            $this->setWidget('visite_date_'.$note->prelevement->getHashForKey(), new sfWidgetFormInput(array(), array()));
            $this->setValidator('visite_date_'.$note->prelevement->getHashForKey(), new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => false)));
            
            $this->setWidget('visite_heure_'.$note->prelevement->getHashForKey(), new sfWidgetFormInput(array(), array()));
            $this->setValidator('visite_heure_'.$note->prelevement->getHashForKey(), new sfValidatorTime(array('time_output' => 'H:i', 'time_format' => '~(?P<hour>\d{2}):(?P<minute>\d{2})~', 'required' => false)));
        }
        $this->widgetSchema->setNameFormat('degustation_courrier[%s]');
    }

    protected function updateDefaults() {
        foreach ($this->getObject()->getNotes() as $note) {
            if ($note->prelevement->exist('type_courrier')) {
                $this->setDefault($note->prelevement->getHashForKey(), $note->prelevement->type_courrier);
            }
            if ($note->prelevement->exist('visite_date') && $note->prelevement->get('visite_date')) {
                $dateArr = explode('-', $note->prelevement->visite_date);
                $date = $dateArr[2].'/'.$dateArr[1].'/'.$dateArr[0];
                $this->setDefault('visite_date_'.$note->prelevement->getHashForKey(), $date);
            }
             if ($note->prelevement->visite_heure) {
                $this->setDefault('visite_heure_'.$note->prelevement->getHashForKey(), $note->prelevement->visite_heure);
            }
        }
    }

    public function getTypesCourrier() {
        return array_merge(array("" => ""), DegustationClient::$types_courrier_libelle);
    }

    public function update() {
        $values = $this->values;
        foreach ($values as $key => $value) {
            if (preg_match('/^\-operateurs.*/', $key) && $value) {
                $realKey = str_replace('-', '/', $key);
                $this->getObject()->getOrAdd($realKey)->add('type_courrier', $value);
                if($value == DegustationClient::COURRIER_TYPE_VISITE){
                    $this->getObject()->getOrAdd($realKey)->add('visite_date', $values['visite_date_'.$key]);
                    $this->getObject()->getOrAdd($realKey)->add('visite_heure', $values['visite_heure_'.$key]);
                }else{
                    $this->getObject()->getOrAdd($realKey)->add('visite_date', null);
                    $this->getObject()->getOrAdd($realKey)->add('visite_heure', null);
                }
                $this->getObject()->getOrAdd($realKey)->add('courrier_envoye', false);
            }            
        }
    }

}

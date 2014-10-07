<?php

class DRevPrelevementForm extends acCouchdbObjectForm
{
    public function configure() {
       $this->setWidgets(array(
            "date" => new sfWidgetFormInput(array(), array("data-date-defaultDate" => $this->getDefaultDate())),
        ));

        $this->setValidators(array(
            'date' => new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)),
        ));

                $this->validatorSchema['date']->setMessage('required', 'La semaine de degustation est obligatoire.');
        $this->widgetSchema["date"]->setLabel('Semaine du');
    }

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();

        if($this->getValidator('date') instanceof sfValidatorDate) {
            $this->setDefault('date', $this->getObject()->getDateFr());
        }
    }

    protected function getDefaultDate() {
        if($this->getObject()->date_precedente) {
            
            $date = new DateTime($this->getObject()->date_precedente);
            $date->modify("+1 year");

            return $date->format("d/m/Y");
        }

        if($this->getObject()->getKey() == DRev::BOUTEILLE_VTSGN) {
            
            return sprintf('01/01/%s', $this->getObject()->getDocument()->campagne + 2);
        }

        if($this->getObject()->getKey() == DRev::CUVE_ALSACE) {

            return sprintf('15/11/%s', $this->getObject()->getDocument()->campagne);
        }

        return sprintf('01/01/%s', $this->getObject()->getDocument()->campagne + 1);
    }
}
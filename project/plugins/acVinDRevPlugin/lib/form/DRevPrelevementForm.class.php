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

        return '01-01-2015';
    }
}
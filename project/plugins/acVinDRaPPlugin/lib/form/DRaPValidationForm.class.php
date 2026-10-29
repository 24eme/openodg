<?php

class DRaPValidationForm extends acCouchdbObjectForm {

    public function configure() {

        if($this->getObject()->isPapier()) {
            $this->setWidget('date', new sfWidgetFormInput());
            $this->setValidator('date', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));
            $this->getWidget('date')->setLabel("Date de réception du document");
            $this->getValidator('date')->setMessage("required", "La date de réception du document est requise");
        }

        $this->setWidget('observations',new bsWidgetFormTextarea(array(), array('style' => 'width: 100%;resize:none;')));
        $this->setValidator('observations',new sfValidatorString(array('required' => false)));

        if(ParcellaireConfiguration::getInstance()->hasEngagements()) {
            $engagements = $this->getOption('engagements');

            foreach ($engagements as $engagement) {
                $this->setWidget('engagement_'.$engagement->getCode(), new sfWidgetFormInputCheckbox());
                $this->setValidator('engagement_'.$engagement->getCode(), new sfValidatorBoolean(array('required' => true)));
                $this->getValidator('engagement_'.$engagement->getCode())->setMessage("required", "Veuillez prendre connaissances de tous les engagements");
            }
        }

        $this->widgetSchema->setNameFormat('parcellaire_validation[%s]');
    }

    protected function doUpdateObject($values) {
		parent::doUpdateObject($values);
        if($this->getObject()->isPapier()) {
            $this->getObject()->validate($values['date']);
        } else {
        	$this->getObject()->validate();
        }
    }

}

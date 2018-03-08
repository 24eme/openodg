<?php

class ParcellaireIrrigableValidationForm extends acCouchdbObjectForm {

    public function configure() {

        if($this->getObject()->isPapier()) {
            $this->setWidget('date', new sfWidgetFormInput());
            $this->setValidator('date', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));
            $this->getWidget('date')->setLabel("Date de réception du document");
            $this->getValidator('date')->setMessage("required", "La date de réception du document est requise");
        }

        if (sfConfig::get('app_document_validation_signataire')) {
        	$this->setWidget('signataire', new sfWidgetFormInput());
    		$this->setValidator('signataire', new sfValidatorString(array('required' => true)));
    		$this->getWidget('signataire')->setLabel("Veuillez préciser votre identité");
            $this->getValidator('signataire')->setMessage("required", "L'identité du signataire est requise");
        }

        $this->setWidget('observations',new bsWidgetFormTextarea(array(), array('style' => 'width: 100%;resize:none;')));
        $this->setValidator('observations',new sfValidatorString(array('required' => false)));

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

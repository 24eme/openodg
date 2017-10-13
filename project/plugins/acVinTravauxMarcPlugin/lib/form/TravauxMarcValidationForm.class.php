<?php

class TravauxMarcValidationForm extends acCouchdbForm
{
	public function configure()
    {
        if($this->getDocument()->isPapier()) {
            $this->setWidget('date', new sfWidgetFormInput());
            $this->setValidator('date', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));
            $this->getWidget('date')->setLabel("Date de réception du document");
            $this->getValidator('date')->setMessage("required", "La date de réception du document est requise");
            $this->getValidator('date')->setMessage('bad_format', "Le format de la date n'est pas correct");
        }

        $this->widgetSchema->setNameFormat('travaux_validation[%s]');
    }

}

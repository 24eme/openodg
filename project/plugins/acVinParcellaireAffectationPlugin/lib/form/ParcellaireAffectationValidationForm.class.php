<?php

class ParcellaireAffectationValidationForm extends acCouchdbObjectForm {

    public function configure() {

        if($this->getObject()->isPapier()) {
            $this->setWidget('date', new sfWidgetFormInput());
            $this->setValidator('date', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));
            $this->getWidget('date')->setLabel("Date de réception du document");
            $this->getValidator('date')->setMessage("required", "La date de réception du document est requise");
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
        foreach ($this->getObject()->getParcelles() as $parcelle) {
            if ($parcelle->exist('affectation') && $parcelle->affectation) {
                $parcelle->date_affectation = date('Y-m-d');
            }
        }
    }

}

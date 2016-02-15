<?php

class ParcellaireValidationForm extends acCouchdbObjectForm {

    public function configure() {
        if(!$this->getObject()->isPapier()) {
            $this->setWidget('autorisation_acheteur', new sfWidgetFormInputCheckbox());
            $this->setValidator('autorisation_acheteur', new sfValidatorBoolean());
        }

        if($this->getObject()->isPapier()) {
            $this->setWidget('date', new sfWidgetFormInput());
            $this->setValidator('date', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));
            $this->getWidget('date')->setLabel("Date de réception du document");
            $this->getValidator('date')->setMessage("required", "La date de réception du document est requise");
        }


        if($this->getObject()->hasVtsgn()) { 
            $this->setWidget('engagement_vtsgn', new sfWidgetFormInputCheckbox());
            $this->setValidator('engagement_vtsgn', new sfValidatorBoolean(array('required' => true)));
            $this->getWidget("engagement_vtsgn")->setLabel("Je m'engage à respecter les conditions de production des mentions VT/SGN et les modalités de contrôle qui y sont liées.");
        }
        
        $this->widgetSchema->setNameFormat('parcellaire_validation[%s]');
    }

    protected function doUpdateObject($values) {
        if(!$this->getObject()->isPapier()) {
            $this->getObject()->autorisation_acheteur = $values['autorisation_acheteur'];
            $this->getObject()->validate();

            return;
        }

        if($this->getObject()->isPapier()) {
            $this->getObject()->autorisation_acheteur = false;
            $this->getObject()->validate($values['date']);

            return;
        }
    }

}

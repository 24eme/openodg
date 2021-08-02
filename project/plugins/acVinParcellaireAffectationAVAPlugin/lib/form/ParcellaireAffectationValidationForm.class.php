<?php

class ParcellaireAffectationValidationForm extends acCouchdbObjectForm {

    public function configure() {
        if(!$this->getObject()->isPapier() && $this->getObject()->hasAcheteursExternes()) {
            $this->setWidget('autorisation_acheteur', new sfWidgetFormInputCheckbox());
            $this->setValidator('autorisation_acheteur', new sfValidatorBoolean());
        }

        if($this->getObject()->isPapier()) {
            $this->setWidget('date', new sfWidgetFormInput());
            $this->setValidator('date', new sfValidatorDate(array('date_output' => 'Y-m-d', 'date_format' => '~(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})~', 'required' => true)));
            $this->getWidget('date')->setLabel("Date de réception du document");
            $this->getValidator('date')->setMessage("required", "La date de réception du document est requise");
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

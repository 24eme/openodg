<?php

class ParcellaireValidationForm extends acCouchdbObjectForm {

    public function configure() {
        $this->setWidget('autorisation_acheteur', new sfWidgetFormInputCheckbox());
        $this->setValidator('autorisation_acheteur', new sfValidatorBoolean());
        
                        
        $this->widgetSchema->setNameFormat('parcellaire_validation[%s]');
    }

    protected function doUpdateObject($values) {
        $this->getObject()->autorisation_acheteur = $values['autorisation_acheteur'];
        $this->getObject()->validate();
    }

}

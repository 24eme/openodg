<?php

class TourneeValidationForm extends acCouchdbObjectForm {

    public function configure() {
        $this->widgetSchema->setNameFormat('tournee_validation[%s]');

        $this->setWidget('nombre_commissions', new sfWidgetFormInput());
        $this->setValidator('nombre_commissions', new sfValidatorInteger(array("required" => true)));
    }

}

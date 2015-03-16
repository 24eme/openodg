<?php

class DegustationValidationForm extends acCouchdbObjectForm {

    public function configure() {
        $this->widgetSchema->setNameFormat('degustation_validation[%s]');
    }

}

<?php

class DegustationValidationForm extends acCouchdbObjectForm {

    public function configure() {
        $this->widgetSchema->setNameFormat('degustation_validation[%s]');
    }

    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);

        $this->getObject()->validation = date('Y-m-d');
    }

}

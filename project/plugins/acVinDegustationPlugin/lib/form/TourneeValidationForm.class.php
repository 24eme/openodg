<?php

class TourneeValidationForm extends acCouchdbObjectForm {

    public function configure() {
        $this->widgetSchema->setNameFormat('tournee_validation[%s]');
    }

    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);

        $this->getObject()->validation = date('Y-m-d');
        $this->getObject()->generatePrelevements();
        $this->getObject()->generateDegustations();
    }

}

<?php

class TourneeValidationForm extends acCouchdbForm {

    public function configure() {
        $this->widgetSchema->setNameFormat('tournee_validation[%s]');
    }

}

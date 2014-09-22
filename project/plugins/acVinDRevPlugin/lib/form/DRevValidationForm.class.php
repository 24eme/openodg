<?php

class DRevValidationForm extends acCouchdbForm
{

    public function configure() {
        
        $this->widgetSchema->setNameFormat('validation[%s]');
    }

}
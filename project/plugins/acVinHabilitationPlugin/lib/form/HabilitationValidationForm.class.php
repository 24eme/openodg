<?php

class HabilitationValidationForm extends acCouchdbForm
{
    public function configure() {


        $this->widgetSchema->setNameFormat('validation[%s]');
    }
}

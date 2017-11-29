<?php

class CompteRechercheForm extends BaseForm 
{
    public function configure() {
        $this->setWidgets(array(
            "q" => new sfWidgetFormInput(),
        ));

        $this->setValidators(array(
            "q" => new sfValidatorString(array("required" => false)),
        ));

        $this->widgetSchema->setNameFormat('%s');
    }
}

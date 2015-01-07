<?php

class CompteRechercheForm extends BaseForm 
{
    public function configure() {
        $this->setWidgets(array(
            "q" => new sfWidgetFormInput(),
        	"all" => new sfWidgetFormInputCheckbox()
        ));

        $this->setValidators(array(
            "q" => new sfValidatorString(array("required" => false)),
        	"all" => new ValidatorBoolean()
        ));

        $this->widgetSchema->setNameFormat('%s');
    }
}

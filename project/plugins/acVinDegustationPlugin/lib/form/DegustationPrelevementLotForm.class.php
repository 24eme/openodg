<?php

class DegustationPrelevementLotForm extends BaseForm {

    public function configure() {
	    	$this->setWidgets(array(
	    			'preleve' => new WidgetFormInputCheckbox(),
	    	));

    	$this->setValidators(array(
    			'preleve' => new ValidatorBoolean(),
    	));
        $this->widgetSchema->setNameFormat('lot[%s]');
    }

}

<?php

class DegustationPreleveLotForm extends BaseForm {

    public function __construct($lot, $defaults = array(), $options = array(), $CSRFSecret = null)
    {
        parent::__construct($defaults, $options, $CSRFSecret);
    }

    public function configure() {
    	$this->setWidgets(array(
    			'preleve' => new WidgetFormInputCheckbox(),
    	));

    	$this->setValidators(array(
    			'preleve' => new ValidatorBoolean(),
    	));
    }

}

<?php

class DRaPProduitDestinationsForm extends acCouchdbObjectForm {

    public function configure() {

    	$this->setWidgets(array(
    			'appellation_destination' => new bsWidgetFormInput(),
    	));

    	$this->setValidators(array(
    			'appellation_destination' => new sfValidatorString(array('required' => false)),
    	));
        $this->widgetSchema->setNameFormat('parcellaire_destinations[%s]');
    }

}

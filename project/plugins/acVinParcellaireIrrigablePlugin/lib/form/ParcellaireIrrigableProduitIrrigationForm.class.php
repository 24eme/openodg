<?php

class ParcellaireIrrigableProduitIrrigationForm extends acCouchdbObjectForm {

    public function configure() {

    	$this->setWidgets(array(
    			'materiel' => new bsWidgetFormInput(),
    			'ressource' => new bsWidgetFormInput(),
    	));

    	$this->setValidators(array(
    			'materiel' => new sfValidatorString(array('required' => false)),
    			'ressource' => new sfValidatorString(array('required' => false)),
    	));
        $this->widgetSchema->setNameFormat('parcellaire_irrigation[%s]');
    }

}

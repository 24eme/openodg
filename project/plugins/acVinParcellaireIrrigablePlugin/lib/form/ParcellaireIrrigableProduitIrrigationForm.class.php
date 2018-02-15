<?php

class ParcellaireIrrigableProduitIrrigationForm extends acCouchdbObjectForm {

    public function configure() {

    	$this->setWidgets(array(
    			'annee_plantation' => new bsWidgetFormInput(),
    			'materiel' => new bsWidgetFormInput(),
    			'ressource' => new bsWidgetFormInput(),
    			'observations' => new bsWidgetFormInput(),
    	));
    	
    	$this->setValidators(array(
    			'annee_plantation' => new sfValidatorString(array('required' => false)),
    			'materiel' => new sfValidatorString(array('required' => false)),
    			'ressource' => new sfValidatorString(array('required' => false)),
    			'observations' => new sfValidatorString(array('required' => false)),
    	));
        $this->widgetSchema->setNameFormat('parcellaire_irrigation[%s]');
    }

}

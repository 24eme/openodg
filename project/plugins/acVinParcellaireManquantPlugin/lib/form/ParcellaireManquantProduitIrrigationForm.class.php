<?php

class ParcellaireManquantProduitIrrigationForm extends acCouchdbObjectForm {

    public function configure() {

    	$this->setWidgets(array(
    			'densite' => new bsWidgetFormInput(),
    			'pourcentage' => new bsWidgetFormInput(),
    	));

    	$this->setValidators(array(
    			'densite' => new sfValidatorString(array('required' => false)),
    			'pourcentage' => new sfValidatorString(array('required' => false)),
    	));
        $this->widgetSchema->setNameFormat('parcellaire_manquant[%s]');
    }
}

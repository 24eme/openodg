<?php

class ParcellaireAffectationProduitAffecteForm extends acCouchdbObjectForm {

    public function configure() {
	    	$this->setWidgets(array(
	    			'affectation' => new WidgetFormInputCheckbox(),
	    			'superficie_affectation' => new sfWidgetFormInputFloat(),
	    	));

    	$this->setValidators(array(
    			'affectation' => new ValidatorBoolean(),
    			'superficie_affectation'=> new sfValidatorNumber(array('required' => false))
    	));
        $this->widgetSchema->setNameFormat('parcellaire_affectation[%s]');
    }

}

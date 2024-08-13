<?php

class ParcellaireIntentionAffectationProduitAffecteForm extends acCouchdbObjectForm {

    public function configure() {
	    	$this->setWidgets(array(
	    			'affectation' => new WidgetFormInputCheckbox(),
	    			'superficie' => new sfWidgetFormInputFloat(array(), array("disabled" => "disabled")),
	    	));

    	$this->setValidators(array(
    			'affectation' => new ValidatorBoolean(),
    			'superficie'=> new sfValidatorNumber(array('required' => false))
    	));
        $this->widgetSchema->setNameFormat('parcellaire_affectation[%s]');
    }

}

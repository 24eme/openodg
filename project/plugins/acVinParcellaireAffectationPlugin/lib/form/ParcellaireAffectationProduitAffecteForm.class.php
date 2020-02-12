<?php

class ParcellaireAffectationProduitAffecteForm extends acCouchdbObjectForm {

    public function configure() {
	    	$this->setWidgets(array(
	    			'affectation' => new WidgetFormInputCheckbox(),
	    	));
    	$this->setValidators(array(
    			'affectation' => new ValidatorBoolean(),
    	));
        $this->widgetSchema->setNameFormat('parcellaire_affectation[%s]');
    }

}

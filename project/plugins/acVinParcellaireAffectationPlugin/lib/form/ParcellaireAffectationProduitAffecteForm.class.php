<?php

class ParcellaireAffectationProduitAffecteForm extends acCouchdbObjectForm {

    public function configure() {
	    	$this->setWidgets(array(
	    			'affectee' => new WidgetFormInputCheckbox(),
	    	));

    	$this->setValidators(array(
    			'affectee' => new ValidatorBoolean(),
    	));
        $this->widgetSchema->setNameFormat('parcellaire_affectation[%s]');
    }

}

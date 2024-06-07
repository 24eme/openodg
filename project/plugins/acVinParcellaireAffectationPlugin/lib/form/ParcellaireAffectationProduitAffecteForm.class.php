<?php

class ParcellaireAffectationProduitAffecteForm extends acCouchdbObjectForm {

    public function configure() {
	    	$this->setWidgets(array(
                    'affectee' => new WidgetFormInputCheckbox(),
	    			'superficie_affectation' => new bsWidgetFormInputFloat(),
	    	));

    	$this->setValidators(array(
            'affectee' => new ValidatorBoolean(),
    			'superficie_affectation' => new sfValidatorNumber(),
    	));
        $this->widgetSchema->setNameFormat('parcellaire_affectation[%s]');
    }

}

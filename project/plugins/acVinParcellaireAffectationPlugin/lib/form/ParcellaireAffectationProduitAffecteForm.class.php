<?php

class ParcellaireAffectationProduitAffecteForm extends acCouchdbObjectForm {

    public function configure() {
	    	$this->setWidgets(array(
                    'affectee' => new WidgetFormInputCheckbox(),
	    			'superficie' => new bsWidgetFormInputFloat(),
	    	));

    	$this->setValidators(array(
            'affectee' => new ValidatorBoolean(),
			'superficie' => new sfValidatorNumber(),
    	));
        $this->widgetSchema->setNameFormat('parcellaire_affectation[%s]');
    }

}

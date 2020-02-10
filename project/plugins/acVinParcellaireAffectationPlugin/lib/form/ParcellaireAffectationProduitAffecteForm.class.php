<?php

class ParcellaireAffectationProduitAffecteForm extends acCouchdbObjectForm {

    public function configure() {
		if ($this->getObject()->active) {
			$this->setWidgets(array(
					'affectation' => new sfWidgetFormInputHidden(),
			));
		} else {
	    	$this->setWidgets(array(
	    			'affectation' => new WidgetFormInputCheckbox(),
	    	));
		}
    	$this->setValidators(array(
    			'affectation' => new ValidatorBoolean(),
    	));
        $this->widgetSchema->setNameFormat('parcellaire_irrigation[%s]');
    }

}

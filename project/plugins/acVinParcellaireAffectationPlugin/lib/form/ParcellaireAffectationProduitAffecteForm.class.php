<?php

class ParcellaireAffectationProduitAffecteForm extends acCouchdbObjectForm {

    public function configure() {
		if ($this->getObject()->affectation && $this->getObject()->date_affectation && $this->getObject()->getDocument()->date && $this->getObject()->date_affectation != $this->getObject()->getDocument()->date) {
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
        $this->widgetSchema->setNameFormat('parcellaire_affectation[%s]');
    }

}

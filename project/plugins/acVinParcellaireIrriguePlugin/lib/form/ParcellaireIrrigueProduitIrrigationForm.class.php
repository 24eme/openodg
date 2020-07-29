<?php

class ParcellaireIrrigueProduitIrrigationForm extends acCouchdbObjectForm {

    public function configure() {
		if ($this->getObject()->irrigation && (!$this->getObject()->getDocument()->exist('papier') || !$this->getObject()->getDocument()->papier)) {
			$this->setWidgets(array(
					'irrigation' => new sfWidgetFormInputHidden(),
			));
		} else {
	    	$this->setWidgets(array(
	    			'irrigation' => new WidgetFormInputCheckbox(),
	    	));
		}
    	$this->setValidators(array(
    			'irrigation' => new ValidatorBoolean(),
    	));
        $this->widgetSchema->setNameFormat('parcellaire_irrigation[%s]');
    }

}

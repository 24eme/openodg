<?php

class ParcellaireManquantParcelleInfoForm extends acCouchdbObjectForm {

    public function configure() {

    	$this->setWidgets(array(
            'densite' => new bsWidgetFormInputInteger(),
            'pourcentage' => new bsWidgetFormInputFloat(),
    	));

    	$this->setValidators(array(
            'densite' => new sfValidatorInteger(array('required' => false)),
            'pourcentage' => new sfValidatorNumber(array('required' => false)),
    	));
        $this->widgetSchema->setNameFormat('parcellaire_manquant[%s]');
    }
}

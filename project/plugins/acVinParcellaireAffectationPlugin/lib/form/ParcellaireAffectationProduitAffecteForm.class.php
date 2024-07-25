<?php

class ParcellaireAffectationProduitAffecteForm extends acCouchdbObjectForm {
    protected $etablissement = null;
    public function __construct(acCouchdbJson $object, $etablissement, $options = array(), $CSRFSecret = null) {
        $this->etablissement = $etablissement;
        parent::__construct($object, $options, $CSRFSecret);
    }
    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        $this->setDefault('superficie', $this->getObject()->getSuperficie($this->etablissement->identifiant));
    }
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

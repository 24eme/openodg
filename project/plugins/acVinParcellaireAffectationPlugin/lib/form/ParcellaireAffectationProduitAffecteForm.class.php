<?php

class ParcellaireAffectationProduitAffecteForm extends acCouchdbObjectForm {
    protected $destinataire = null;
    public function __construct(acCouchdbJson $object, $destinataire, $options = array(), $CSRFSecret = null) {
        $this->destinataire = $destinataire;
        parent::__construct($object, $options, $CSRFSecret);
    }
    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        $superficie = $this->getObject()->getSuperficie($this->destinataire->identifiant);
        $this->setDefault('affectee', boolval($superficie));
        $this->setDefault('superficie', ($superficie !== null) ? $superficie : $this->getObject()->getSuperficieParcellaireAffectable());
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

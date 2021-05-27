<?php

class ParcellaireAffectationCoopSaisieForm extends ParcellaireAffectationProduitsForm {

    public function __construct($affectationParcellaire, $coop)
    {
        parent::__construct($affectationParcellaire);

        if (sfConfig::get('app_document_validation_signataire')) {
            $this->setDefault('signataire', $coop->raison_sociale);
        }
    }

    public function configure() {
		parent::configure();
        if (sfConfig::get('app_document_validation_signataire')) {
        	$this->setWidget('signataire', new sfWidgetFormInput());
    		$this->setValidator('signataire', new sfValidatorString(array('required' => true)));
    		$this->getWidget('signataire')->setLabel("Nom et prénom :");
            $this->getValidator('signataire')->setMessage("required", "Le nom et prénom du signataire est requise");
        }
        $this->setWidget('observations',new bsWidgetFormTextarea(array(), array('style' => 'width: 100%;resize:none;')));
        $this->setValidator('observations',new sfValidatorString(array('required' => false)));
    }


    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);
    }

}

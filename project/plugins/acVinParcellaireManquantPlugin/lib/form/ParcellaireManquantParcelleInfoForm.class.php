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

    protected function updateDefaultsFromObject() {
        parent::updateDefaultsFromObject();
        $parcelle = $this->getObject();
        if(!$this->getObject() instanceof ParcellaireAffectationProduitDetail) {
            $parcelle = $this->getObject()->getParent();
            $parcelleParcellaire = $parcelle->getParcelleParcellaire();

            if($parcelleParcellaire) {
                $this->setDefault('densite', $parcelleParcellaire->getDensite());
            }
        }
    }
}

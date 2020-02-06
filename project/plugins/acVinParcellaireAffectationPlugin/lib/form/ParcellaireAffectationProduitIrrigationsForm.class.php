<?php

class ParcellaireAffectationProduitIrrigationsForm extends acCouchdbObjectForm {

    public function configure() {
		foreach ($this->getObject()->detail as $key => $value) {
			$this->embedForm($key, new ParcellaireAffectationProduitIrrigationForm($value));
		}
        $this->widgetSchema->setNameFormat('[%s]');
    }

}

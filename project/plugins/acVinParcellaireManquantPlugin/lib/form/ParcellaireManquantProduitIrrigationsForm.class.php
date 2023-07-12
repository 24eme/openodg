<?php

class ParcellaireManquantProduitIrrigationsForm extends acCouchdbObjectForm {

    public function configure() {
		foreach ($this->getObject()->detail as $key => $value) {
			$this->embedForm($key, new ParcellaireManquantProduitIrrigationForm($value));
		}
        $this->widgetSchema->setNameFormat('[%s]');
    }

}

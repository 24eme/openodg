<?php

class ParcellaireIrrigueProduitIrrigationsForm extends acCouchdbObjectForm {

    public function configure() {
		foreach ($this->getObject()->detail as $key => $value) {
			$this->embedForm($key, new ParcellaireIrrigueProduitIrrigationForm($value));
		}
        $this->widgetSchema->setNameFormat('[%s]');
    }

}

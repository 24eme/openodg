<?php

class ParcellaireIrrigableProduitIrrigationsForm extends acCouchdbObjectForm {

    public function configure() {
		foreach ($this->getObject()->detail as $key => $value) {
			$this->embedForm($key, new ParcellaireIrrigableProduitIrrigationForm($value));
		}
        $this->widgetSchema->setNameFormat('[%s]');
    }

}

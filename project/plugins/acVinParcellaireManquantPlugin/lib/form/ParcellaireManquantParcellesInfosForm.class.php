<?php

class ParcellaireManquantParcellesInfosForm extends acCouchdbObjectForm {

    public function configure() {
		foreach ($this->getObject()->detail as $key => $value) {
			$this->embedForm($key, new ParcellaireManquantParcelleInfoForm($value));
		}
        $this->widgetSchema->setNameFormat('[%s]');
    }

}

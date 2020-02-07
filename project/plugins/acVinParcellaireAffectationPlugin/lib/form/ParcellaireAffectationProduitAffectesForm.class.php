<?php

class ParcellaireAffectationProduitAffectesForm extends acCouchdbObjectForm {

    public function configure() {
		foreach ($this->getObject()->detail as $key => $value) {
			$this->embedForm($key, new ParcellaireAffectationProduitAffecteForm($value));
		}
        $this->widgetSchema->setNameFormat('[%s]');
    }

}

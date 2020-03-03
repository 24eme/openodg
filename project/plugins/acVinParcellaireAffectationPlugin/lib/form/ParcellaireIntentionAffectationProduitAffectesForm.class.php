<?php

class ParcellaireIntentionAffectationProduitAffectesForm extends acCouchdbObjectForm {

    public function configure() {
		foreach ($this->getObject()->detail as $key => $value) {
			$this->embedForm($key, new ParcellaireIntentionAffectationProduitAffecteForm($value));
		}
        $this->widgetSchema->setNameFormat('[%s]');
    }

}

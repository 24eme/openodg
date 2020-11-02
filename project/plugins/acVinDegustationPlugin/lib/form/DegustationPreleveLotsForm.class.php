<?php

class DegustationPreleveLotsForm extends acCouchdbForm {

    public function configure() {
        $formLots = new BaseForm();
		foreach ($this->getDocument()->lots as $key => $lot) {
			if ($lot->isLeurre()) {
				continue;
			}
			$formLots->embedForm($key, new DegustationPreleveLotForm($lot));
		}
        $this->embedForm('lots', $formLots);
        $this->widgetSchema->setNameFormat('preleve[%s]');

    }

    public function save() {
        $values = $this->getValues();
        foreach($values['lots'] as $key => $value) {
            if(!isset($value['preleve']) || !$value['preleve']) {
                $this->getDocument()->lots->get($key)->statut = Lot::STATUT_ATTENTE_PRELEVEMENT;
                continue;
            }
            $this->getDocument()->lots->get($key)->statut = Lot::STATUT_PRELEVE;
        }

        $this->getDocument()->save();
    }
}

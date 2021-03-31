<?php

class DegustationPreleveLotsForm extends acCouchdbForm {

    public function configure() {
        $formLots = new BaseForm();
        $lots = ($this->getDocument()->lots)->toArray();

        uasort($lots, array($this, 'cmp'));
    		foreach ($lots as $key => $lot) {
    			if ($lot->isLeurre()) {
    				continue;
    			}
    			$formLots->embedForm($key, new DegustationPreleveLotForm($lot));
    		}
        $this->embedForm('lots', $formLots);
        $this->widgetSchema->setNameFormat('preleve[%s]');

    }

    public function cmp($a, $b) {
        if ($a->destination_date == $b->destination_date) {
            return 0;
        }
        return ($a->destination_date < $b->destination_date) ? -1 : 1;
    }

    public function save() {
        $values = $this->getValues();
        foreach($values['lots'] as $key => $value) {
            if(!isset($value['preleve']) || !$value['preleve']) {
                $this->getDocument()->lots->get($key)->statut = Lot::STATUT_ATTENTE_PRELEVEMENT;
                continue;
            }
            if(!$this->getDocument()->lots->get($key)->isPreleve()){
              $this->getDocument()->lots->get($key)->setIsPreleve();
            }
        }

        $this->getDocument()->save();
    }
}

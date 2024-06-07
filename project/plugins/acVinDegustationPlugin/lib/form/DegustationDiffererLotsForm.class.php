<?php

class DegustationDiffererLotsForm extends acCouchdbForm {

    public function configure() {
        $formLots = new BaseForm();
        $lots = array();
        foreach( ($this->getDocument()->lots)->toArray() as $k => $l ) {
            $lots[$l->campagne.$l->numero_dossier.$l->adresse_logement.$l->unique_id] = array('lot' => $l, 'key' => $k);
        }
        ksort($lots);

        foreach (array_values($lots) as $v) {
            $lot = $v['lot'];
            $key = $v['key'];
            if ($lot->isLeurre()||$lot->isAnnule()||!$lot->isPreleve()) {
                continue;
            }
            $formLots->embedForm($key, new DegustationPreleveLotForm($lot, ['preleve' => $lot->isDiffere()]));
        }

        $this->embedForm('lots', $formLots);
        $this->widgetSchema->setNameFormat('differer[%s]');

    }

    public function save() {
        $values = $this->getValues();
        foreach($values['lots'] as $key => $value) {
            if(!isset($value['preleve']) || !$value['preleve']) {
                $this->getDocument()->lots->get($key)->setIsPreleve($this->getDocument()->lots->get($key)->preleve);
            } else {
                $this->getDocument()->lots->get($key)->setIsDiffere();
            }
        }

        $this->getDocument()->save();
    }
}

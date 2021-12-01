<?php

class DegustationPreleveLotsForm extends acCouchdbForm {

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
                $this->getDocument()->lots->get($key)->preleve = null;
                continue;
            }
            if(!$this->getDocument()->lots->get($key)->isPreleve()){
              $this->getDocument()->lots->get($key)->setIsPreleve();
            }
            if($this->getDocument()->lots->get($key)->isAnnule()) {
                $this->getDocument()->lots->get($key)->statut = Lot::STATUT_ANNULE;
                $this->getDocument()->lots->get($key)->setIsPreleve();
            }
        }

        $this->getDocument()->save();
    }
}

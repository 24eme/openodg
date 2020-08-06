<?php

class DegustationPrelevementLotsForm extends acCouchdbObjectForm {

    public function configure() {
        $lotsPrelevables = $this->getLotsPrelevables();
		foreach ($lotsPrelevables as $key => $item) {
			$this->embedForm($key, new DegustationPrelevementLotForm());
		}
        $this->widgetSchema->setNameFormat('prelevement[%s]');
    }


    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);
        $lots = $this->getLotsPrelevables();
        $keys = array_keys($lots);
        if ($this->getObject()->exist('lots')) {
            $this->getObject()->remove('lots');
        }
        $this->getObject()->add('lots');
        foreach ($values as $id => $val) {
            if (!in_array($id, $keys)) {
                continue;
            }
            if (isset($val['preleve']) && !empty($val['preleve'])) {
                $this->getObject()->lots->add(null, $lots[$id]);
            }
        }
    }

    protected function updateDefaultsFromObject() {
        $defaults = $this->getDefaults();
        foreach ($this->getObject()->lots as $lot) {
            $key = $lot->id_document.'-'.$lot->getGenerateKey();
            $defaults[$key] = array('preleve' => 1);
        }
        $this->setDefaults($defaults);
    }
    
    public function getLotsPrelevables() {
        $lots = array();
        foreach (MouvementLotView::getInstance()->getByPrelevablePreleve(1,0)->rows as $item) {
            $lot = MouvementLotView::generateLotByMvt($item->value);
            $lots[$lot->id_document.'-'.Lot::generateKey($lot)] = $lot;
        }
        ksort($lots);
        return $lots;
    }

}

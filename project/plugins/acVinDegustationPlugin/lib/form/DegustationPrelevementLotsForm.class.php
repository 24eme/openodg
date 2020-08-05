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
    }

    protected function updateDefaultsFromObject() {
        $defaults = $this->getDefaults();
        $this->setDefaults($defaults);
    }
    
    public function getLotsPrelevables() {
        $lots = array();
        foreach (MouvementLotView::getInstance()->getByPrelevablePreleve(1,0)->rows as $item) {
            $lot = $item->value;
            $lots[$lot->origine_document_id.'|'.$lot->origine_mouvement] = $lot;
        }
        return $lots;
    }

}

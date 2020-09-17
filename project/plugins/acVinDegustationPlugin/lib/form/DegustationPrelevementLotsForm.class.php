<?php

class DegustationPrelevementLotsForm extends acCouchdbObjectForm {

    private $lotsPrelevables = null;

    public function configure() {
        $this->lotsPrelevables = $this->getObject()->getLotsPrelevables();
        $formLots = new BaseForm();
		foreach ($this->lotsPrelevables as $key => $item) {
			$formLots->embedForm($key, new DegustationPrelevementLotForm());
		}
        $this->embedForm('lots', $formLots);
        $this->widgetSchema->setNameFormat('prelevement[%s]');

    }


    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);
        $mvtkeys = array();
        foreach ($values['lots'] as $id => $val) {
            $mvtkeys[$id] = (isset($val['preleve']) && !empty($val['preleve']));
        }
        $this->getObject()->setLotsFromMvtKeys($mvtkeys, Lot::STATUT_ATTENTE_PRELEVEMENT);
    }

    protected function updateDefaultsFromObject() {
        $defaults = $this->getDefaults();

        foreach ($this->getObject()->lots as $lot) {
          $key = $lot->getGeneratedMvtKey();
          $defaults['lots'][$key] = array('preleve' => 1);
        }

        if(!count($this->getObject()->lots)){
          foreach ($this->lotsPrelevables as $key => $item) {
              $defaults['lots'][$key] = array('preleve' => 1);
          }
        }
        $this->setDefaults($defaults);
    }

    public function getLotsPrelevables() {
        return $this->getObject()->getLotsPrelevables();
    }

}

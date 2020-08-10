<?php

class DegustationPrelevementLotsForm extends acCouchdbObjectForm {

    public function configure() {
        //TODO : supprimer cette goretterie réalisée pour la démo
        $this->campagne = '2019';
        $lotsPrelevables = $this->getLotsPrelevables();
        $formLots = new BaseForm();
		foreach ($lotsPrelevables as $key => $item) {
			$formLots->embedForm($key, new DegustationPrelevementLotForm());
		}
        $this->embedForm('lots', $formLots);
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
        foreach ($values['lots'] as $id => $val) {
            if (!in_array($id, $keys)) {
                continue;
            }
            if (isset($val['preleve']) && !empty($val['preleve'])) {
                $lot = $this->getObject()->lots->add(null, $lots[$id]);
            }
        }
    }

    protected function updateDefaultsFromObject() {
        $defaults = $this->getDefaults();
        foreach ($this->getObject()->lots as $lot) {
            $key = $lot->getGenerateKey();
            $defaults['lots'][$key] = array('preleve' => 1);
        }
        $this->setDefaults($defaults);
    }

    public function getLotsPrelevables() {
         $lots = array();
         foreach (MouvementLotView::getInstance()->getByPrelevablePreleve($this->campagne, 1,0)->rows as $item) {
             $lot = MouvementLotView::generateLotByMvt($item->value);
             $lots[Lot::generateKey($lot)] = $lot;
         }
         ksort($lots);
         return $lots;
     }
}

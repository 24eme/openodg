<?php

class ParcellaireIrrigableProduitsForm extends acCouchdbObjectForm {

    public function configure() {
        foreach ($this->getObject()->getParcelles() as $p) {
            $this->embedForm($p->getParcelleId(), new ParcellaireIrrigableProduitIrrigationForm($p));
        }
        $this->widgetSchema->setNameFormat('parcelles[%s]');
    }

    protected function doUpdateObject($values) {
        $parcelles = $this->getObject()->getParcelles();
        foreach ($values as $pid => $value) {
            if (!isset($parcelles[$pid])) {
                continue;
            }
            $node = $parcelles[$pid];
            foreach ($value as $k => $v) {
                $node->add($k, $v);
            }
        }
    }
}

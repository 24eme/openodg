<?php

class ParcellaireAffectationProduitsForm extends acCouchdbObjectForm {

    public function configure() {
		foreach ($this->getParcelles() as $key => $value) {
			$this->embedForm($key, new ParcellaireAffectationProduitAffecteForm($value));
		}

        $this->widgetSchema->setNameFormat('parcelles[%s]');
    }

    public function getParcelles() {
        return $this->getObject()->getParcelles();
    }

    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);
        $parcelles = $this->getParcelles();
        foreach ($values as $pid => $items) {
            if (!isset($parcelles[$pid])){
                continue;
            }
            $parcelle = $parcelles[$pid];
            $node = $this->getObject()->declaration->get(str_replace('/declaration/', '', $parcelle->produit_hash));
            $node = $node->detail->get($pid);
            foreach ($items as $k => $v) {
                $node->add($k, $v);
            }
        }
    }

}

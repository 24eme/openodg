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
        $this->getObject()->remove('declaration');
        $this->getObject()->add('declaration');
        foreach ($values as $pid => $items) {
            if (!isset($parcelles[$pid])){
                continue;
            }
            if (!isset($items['affectee']) || !$items['affectee']) {
                continue;
            }
            $parcelle = $parcelles[$pid];
            $node = $this->getObject()->declaration->add(str_replace('/declaration/', '', $parcelle->produit_hash));
            $node->libelle = $node->getConfig()->getLibelleComplet();
            $node = $node->detail->add($pid, $parcelle);
            $node->add('affectee', 1);
            $node->add('superficie', $items['superficie']);
        }
    }

}

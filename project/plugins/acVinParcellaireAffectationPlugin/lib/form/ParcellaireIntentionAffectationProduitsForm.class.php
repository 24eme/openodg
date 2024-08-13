<?php

class ParcellaireIntentionAffectationProduitsForm extends acCouchdbObjectForm {

    public function configure() {
		foreach ($this->getObject()->getParcellesByDgc() as $key => $values) {
            foreach($values as $value)  {
                $parcelle = $this->getObject()->getParcelleFromParcelleReference($value);
                if (!$parcelle) {
                    $parcelle = $value;
                }
                $this->embedForm($value->produit_hash.'/'.$value->parcelle_id, new ParcellaireIntentionAffectationProduitAffecteForm($parcelle));
            }
		}

        $this->widgetSchema->setNameFormat('parcelles[%s]');
    }

    protected function doUpdateObject($values) {
		parent::doUpdateObject($values);
        $obj = $this->getObject();
        $obj->remove('declaration');
        $obj->add('declaration');
        foreach ($obj->getParcellesByDgc() as $dgc_key => $parcelles) {
            foreach($parcelles as $parcelle)  {
                $key = $parcelle->produit_hash.'/'.$parcelle->parcelle_id;
                $value = $values[$key];
                if (!isset($values[$key])) {
                    continue;
                }
                if (!$value['affectation']) {
                    continue;
                }
    			$node = $obj->declaration->add(str_replace('/declaration/', '', $parcelle->produit_hash));
    			$node = $node->detail->add($parcelle->parcelle_id);
                ParcellaireClient::CopyParcelle($node, $parcelle);
                $node->affectation = 1;
                if (!$node->date_affectation) {
                    $node->date_affectation = date('Y-m-d');
                }
                if ($value['superficie']) {
                    $node->superficie = $value['superficie'];
                }else{
                    $node->superficie = $value->getSuperficieParcellaire();
                }
            }
        }
    }

}

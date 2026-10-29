<?php

class ParcellaireIntentionAffectationProduitsForm extends acCouchdbObjectForm {

    public function configure() {
        foreach ($this->getObject()->declaration->getParcellesByDgc() as $key => $values) {
            foreach($values as $parcelle)  {
                $this->embedForm($parcelle->produit_hash.'/'.$parcelle->parcelle_id, new ParcellaireIntentionAffectationProduitAffecteForm($parcelle));
            }
        }

        $this->widgetSchema->setNameFormat('parcelles[%s]');
    }

    protected function doUpdateObject($values) {
		parent::doUpdateObject($values);
        $obj = $this->getObject();
        foreach ($obj->declaration->getParcellesByDgc() as $dgc_key => $parcelles) {
            foreach($parcelles as $parcelle)  {
                $key = $parcelle->produit_hash.'/'.$parcelle->parcelle_id;
                $value = $values[$key];
                if (!isset($values[$key])) {
                    continue;
                }
                $node = $obj->declaration->add(str_replace('/declaration/', '', $parcelle->produit_hash));
                $node = $node->detail->add($parcelle->parcelle_id);
                if (!$value['affectation']) {
                    $node->affectation = 0;
                    $node->date_affectation = null;
                    $node->superficie = null;
                    continue;
                }
                ParcellaireClient::CopyParcelle($node, $parcelle, true);
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

<?php

class DRevLotsProduitsForm extends acCouchdbObjectForm {

    public function configure() {
        foreach ($this->getObject() as $hash => $produit) {
            $this->embedForm($produit->getKey(), new DRevLotsProduitForm($produit));
        }
    }

    protected function doUpdateObject($values) {
        foreach ($this->getEmbeddedForms() as $key => $embedForm) {
            $embedForm->doUpdateObject($values[$key]);     
        }
        $this->getObject()->getParent()->updateTotal();
    }

}

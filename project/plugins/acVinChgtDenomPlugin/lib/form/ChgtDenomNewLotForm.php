<?php

class ChgtDenomNewLotForm extends LotModificationForm
{
    public function __construct($lot, $chgt, $options = array(), $CSRFSecret = null) {
        $this->chgtDenom = $chgt;
        $this->lot = $lot;
        return parent::__construct($lot, $options, $CSRFSecret);
    }

    public function configure() {
        parent::configure();

        $this->widgetSchema->setNameFormat('chgtdenom_newlot_[%s]');
    }

    protected function doSave($con = NULL) {
        $this->updateObject();
        $lot = $this->getObject();

        $hash = $this->getValue('produit_hash');

        foreach($this->getProduits() as $key => $cepage) {
            if($hash == $key) {
                $lot->produit_libelle = $cepage;
            }
        }

        $this->chgtDenom->setLotOrigine($lot);
        $this->chgtDenom->changement_origine_id_document = null;
        $this->chgtDenom->save();
    }
}

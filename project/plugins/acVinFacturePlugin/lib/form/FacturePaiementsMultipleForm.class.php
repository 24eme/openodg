<?php

class FacturePaiementsMultipleForm extends acCouchdbObjectForm {

    public function configure()
    {
      $this->getObject()->paiements->add();
      $this->embedForm('paiements', new FacturePaiementsForm($this->getObject()));
      $this->widgetSchema->setNameFormat('facture_paiements_multiple[%s]');
    }

    protected function doUpdateObject($values) {
        parent::doUpdateObject($values);
        $this->getObject()->paiements->cleanPaiements();
    }

}

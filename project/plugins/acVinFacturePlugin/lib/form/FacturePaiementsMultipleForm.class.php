<?php

class FacturePaiementsMultipleForm extends acCouchdbForm {

    public function configure()
    {
      $this->getDocument()->add('paiements');
      $this->getDocument()->paiements->add();
      $this->embedForm('paiements', new FacturePaiementsForm($this->getDocument()));
      $this->widgetSchema->setNameFormat('facture_paiements_multiple[%s]');
    }

    public function save() {
        $values = $this->getValues();
        $facture = $this->getDocument();
        foreach($values['paiements'] as $k => $v) {
            if ($v['montant'] == 0 || !($v['type_reglement'])) {
                continue;
            }
            $facture->paiements[$k]->montant = $v['montant'];
            $facture->paiements[$k]->date = $v['date'];
            $facture->paiements[$k]->type_reglement = $v['type_reglement'];
            $facture->paiements[$k]->commentaire = $v['commentaire'];
        }
        if ($facture->paiements[$k]->montant == 0 || !($facture->paiements[$k]->date) || !($facture->paiements[$k]->type_reglement)) {
            $facture->paiements->remove($k);
        }
        $facture->updateMontantPaiement();
        return $facture->save();
    }

}

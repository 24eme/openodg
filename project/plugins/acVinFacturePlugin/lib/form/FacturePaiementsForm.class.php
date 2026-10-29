<?php

class FacturePaiementsForm extends acCouchdbForm {

    public function configure()
    {
      $paiements = $this->getDocument()->getOrAdd('paiements');
      foreach($paiements as $paiement) {
          if (!$paiement->versement_comptable) {
              $this->embedForm($paiement->getKey(), new FacturePaiementEmbedForm($paiement));
          }
      }

        $this->widgetSchema->setNameFormat('facture_paiements[%s]');
    }

}

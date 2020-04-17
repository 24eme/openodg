<?php
/**
 * Model for FacturePaiements
 *
 */

class FacturePaiements extends BaseFacturePaiements {


  public function cleanPaiements() {
      $paiementsToDelete = array();

      foreach($this as $paiement) {
          if(!$paiement->exist('date') || !$paiement->date || !$paiement->exist('montant') || !$paiement->montant) {
              $paiementsToDelete[$paiement->getKey()] = $true;
          }
      }

      foreach($paiementsToDelete as $key => $void) {
          $this->remove($key);
      }

  }

  public function getPaimentsTotal(){
    $total_paiement = 0.0;
    foreach($this as $paiement) {
        if($paiement->exist('date') && $paiement->date && $paiement->exist('montant') && $paiement->montant) {
            $total_paiement+= $paiement->montant;
        }
    }
    return $total_paiement;
  }


}

<?php
class AdelpheCotisationDetail extends BaseAdelpheCotisationDetail {

  public function addDetail($part, $quantite, $prixUnitaire, $prixTotal) {
    $this->part = $part;
    $this->quantite = $quantite;
    $this->prix_unitaire = $prixUnitaire;
    $this->prix = $prixTotal;
  }

}

<?php

class ChgtDenomLot extends BaseChgtDenomLot
{
    public function isDeclassement() {
      return (!$this->changement_produit);
    }
    public function isChgtTotal() {
      return (!$this->changement_volume);
    }
}

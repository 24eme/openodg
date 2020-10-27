<?php

class ChgtDenomLot extends BaseChgtDenomLot
{
  public function getUnicityKey(){
      return KeyInflector::slugify($this->produit_hash.'/'.$this->millesime.'/'.$this->numero.'/'.$this->volume);
  }
}

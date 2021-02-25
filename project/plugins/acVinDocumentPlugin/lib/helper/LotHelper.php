<?php

function showProduitLot($lot) {


  $text = $lot->produit_libelle."<small> ";
  if(DrevConfiguration::getInstance()->hasSpecificiteLot()){
     $text .= ($lot->specificite && $lot->specificite != Lot::SPECIFITE_UNDEFINED)? $lot->specificite : "";
  }

  $text .= ($lot->millesime)? " ".$lot->millesime."" : "";
  $text .= "</small>";
  if(count($lot->cepages)){
    $text .= "<small class=\"text-muted\">".$lot->getCepagesToStr()."</small>";
  }

  return $text;

}

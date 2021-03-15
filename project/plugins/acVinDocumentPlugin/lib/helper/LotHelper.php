<?php

function showProduitLot($lot)
{
  $text = $lot->produit_libelle." <small>";
  $text .= ($lot->millesime) ? $lot->millesime : "";

  if(DrevConfiguration::getInstance()->hasSpecificiteLot() && $lot->specificite && $lot->specificite !== Lot::SPECIFICITE_UNDEFINED){
     $text .= ' - '.$lot->specificite;
  }

  $text .= "</small>";

  if(property_exists($lot, "cepages") && count($lot->cepages)){
    $text .= "<small class=\"text-muted\">".$lot->getCepagesToStr()."</small>";
  }

  // check car dans /prelevement-lots il s'agit de stdClass
  $text .= '<span class="pull-right text-muted">';

  if (get_class($lot) === stdClass::class) {
    $text .= substr($lot->id_document, 0, 4);
  } else {
    $text .= $lot->getProvenance();
  }

  $text .= '</span>';

  return $text;

}

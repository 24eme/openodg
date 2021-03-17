<?php

function showProduitLot($lot)
{
  $text = $lot->produit_libelle." <small>";
  $text .= ($lot->millesime) ? $lot->millesime : "";

  if(DrevConfiguration::getInstance()->hasSpecificiteLot() && $lot->specificite && $lot->specificite !== Lot::SPECIFICITE_UNDEFINED){
     $text .= ' - '.$lot->specificite;
  }

  $text .= "</small>";

  if (get_class($lot) === stdClass::class) {

      if(property_exists($lot, "cepages") && count((array)$lot->cepages)) {

        foreach ($lot->cepages as $cepage => $pourcentage_volume) {
          $text .= " <small class='text-muted'> ".$cepage."</small>";
        }

      }
  } else {
      if($lot->exist("details")) {
        $text .= " <small class='text-muted'>".$lot->details."</small>";
      }
  }

  return $text;

}

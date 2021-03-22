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

function showDetailMvtLot($mvtLot){
    $text = "";
    if(!$mvtLot->value->detail){
        return '<span class="label label-success">'.Lot::$libellesStatuts[$mvtLot->value->statut].'</span>';
    }
    switch ($mvtLot->value->statut) {
        case Lot::STATUT_NONCONFORME :
            return '<span class="label label-danger">'.$mvtLot->value->detail.'</span>';
        case Lot::STATUT_RECOURS_OC :
            return '<span class="label label-warning">'.$mvtLot->value->detail.'</span>';
    }

    return '<span class="label label-success">'.$mvtLot->value->detail.'</span>';
}

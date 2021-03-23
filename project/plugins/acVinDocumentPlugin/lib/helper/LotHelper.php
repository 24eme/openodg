<?php

function showProduitLot($lot)
{

  $text = $lot->produit_libelle." <small>";
  $text .= ($lot->millesime) ? $lot->millesime : "";

  if(DrevConfiguration::getInstance()->hasSpecificiteLot() && $lot->specificite && $lot->specificite !== Lot::SPECIFICITE_UNDEFINED){
     $text .= ' - '.$lot->specificite;
  }

  $text .= "</small>";
  $fromView = ($lot instanceof stdClass);
  if(!$fromView){

    $text .= showOnlyCepages($lot);
  }

  return $text;

}

function showOnlyCepages($lot){
  $text = null;
    if ($lot->cepages) {

        if(count((array)$lot->cepages)) {

          foreach ($lot->cepages as $cepage => $volume_cepage) {
            $text .= " <small class='text-muted'>".number_format($volume_cepage * 100 / $lot->volume, 2, ',', ' ')."% ".$cepage."</small>";
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

function getUrlEtapeFromMvtLot($mvtLot){
    if($mvtLot->value->document_type != DegustationClient::TYPE_MODEL){

        return url_for(strtolower($mvtLot->value->document_type).'_visualisation', array('id' => $mvtLot->value->document_id));
    }

    switch ($mvtLot->value->statut) {
        case Lot::STATUT_NONCONFORME :
        case Lot::STATUT_CONFORME :
            return url_for(strtolower($mvtLot->value->document_type).'_visualisation', array('id' => $mvtLot->value->document_id));

        case Lot::STATUT_ANONYMISE :
            return url_for('degustation_anonymats_etape', array('id' => $mvtLot->value->document_id));

        case Lot::STATUT_DEGUSTE :
            return url_for('degustation_resultats_etape', array('id' => $mvtLot->value->document_id));

    }
    return url_for(strtolower($mvtLot->value->document_type).'_visualisation', array('id' => $mvtLot->value->document_id));
}

function pictoDegustable($lot) {
    if($lot->affectable) {
        return '<span title="À déguster" class="glyphicon glyphicon-ok-sign"></span>';
    }

    return '<span title="Ne sera pas dégusté" style="opacity: 0.7;" class="text-muted glyphicon glyphicon-ban-circle"></span>';
}

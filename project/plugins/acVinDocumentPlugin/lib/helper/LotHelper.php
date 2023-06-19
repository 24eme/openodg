<?php

function showOnlyProduit($lot, $show_always_specificite = true, $tag = 'small')
{

  $text = $lot->produit_libelle." <".$tag.">";
  $text .= ($lot->millesime) ? $lot->millesime : "";
  if ($show_always_specificite || DegustationConfiguration::getInstance()->hasSpecificiteLotPdf()) {
      if($lot->specificite && $lot->specificite !== Lot::SPECIFICITE_UNDEFINED){
          $text .= ' - '.$lot->specificite;
      }
  }else{
      if (strpos($lot->specificite, 'primeur') !== false) {
          $text .= ' - primeur';
      }
  }

  $text .= "</".$tag.">";
  return $text;
}

function showProduitCepagesLot($lot, $show_always_specificite = true, $tagSmall = 'small')
{   $text = "";
    $text .= showOnlyProduit($lot, $show_always_specificite, $tagSmall);
    $text .= showOnlyCepages($lot, null, $tagSmall);
    return $text;
}

function showOnlyCepages($lot, $maxcars = null, $tag = 'small') {
  $text = '';
  $html = " <".$tag." class='text-muted'>";
  if ($lot instanceof stdClass) {
    $total = $lot->volume;
    foreach ($lot->cepages as $cepage => $hl) {
        $text .= $cepage . ' (' . round(($hl*100)/$total, 2) . "%) ";
    }
  } else {
    if ($lot->cepages) {
      $text .= $lot->getCepagesLibelle();
    }
    if($lot->exist("details")) {
        $text .= $lot->details;
    }
  }
  if (!$text) {
    return " <".$tag.">&nbsp;</".$tag.">";
  }
  if ($maxcars) {
      $text = substrUtf8($text, 0, $maxcars);
  }
  $html .= $text;
  $html .= "</".$tag.">";
  return $html;
}

function getUrlEtapeFromMvtLot($mvtLot)
{
    $documentId = $mvtLot->value->document_id;

    if ($mvtLot->value->statut === Lot::STATUT_AFFECTABLE) {
        return url_for('degustation_prelevables');
    }

    if($mvtLot->value->document_type != DegustationClient::TYPE_MODEL){

        return url_for(strtolower($mvtLot->value->document_type).'_visualisation', array('id' => $documentId));
    }

    switch ($mvtLot->value->statut) {
        case Lot::STATUT_MANQUEMENT_EN_ATTENTE:
            return url_for('degustation_manquements');

        case Lot::STATUT_NONCONFORME :
        case Lot::STATUT_CONFORME :
            return url_for(strtolower($mvtLot->value->document_type).'_visualisation', array('id' => $documentId));

        case Lot::STATUT_DEGUSTE :
            return url_for('degustation_resultats_etape', array('id' => $documentId));

        case Lot::STATUT_ANONYMISE :
            return url_for('degustation_anonymats_etape', array('id' => $documentId));

        case Lot::STATUT_ATTABLE:
            return url_for('degustation_organisation_table', [
                'id' => $documentId,
                'numero_table' => ord(substr($mvtLot->value->detail, -1)) - 64
            ]);

        case Lot::STATUT_PRELEVE:
        case Lot::STATUT_ATTENTE_PRELEVEMENT:
            return url_for('degustation_preleve', ['id' => $documentId]);

        case Lot::STATUT_AFFECTE_DEST:
            return url_for('degustation_selection_lots', ['id' => $documentId]);
    }
    return url_for(strtolower($mvtLot->value->document_type).'_visualisation', array('id' => $documentId));
}

function pictoDegustable($lot) {
    $lot = $lot->getLotInDrevOrigine();

    if($lot->isCommercialisable() === false) {
        return '<span title="Non commercialisable" class="glyphicon glyphicon-remove-circle text-danger"></span>';
    }

    if($lot->id_document_affectation) {
        return '<span title="Dégusté" class="glyphicon glyphicon-ok-circle text-success"></span>';
    }

    if($lot->affectable) {
        return '<span title="À déguster" class="glyphicon glyphicon-time text-success"></span>';
    }

    return '<span title="Réputé conforme" style="opacity: 0.5;" class="text-muted glyphicon glyphicon-ok"></span>';
}

function showLotStatusCartouche($statut, $detail = null, $secondPassage = false) {
    if (!isset(Lot::$libellesStatuts[$statut]) && !$detail) {
        return ;
    }
    $labelClass = isset(Lot::$statut2label[$statut]) ? Lot::$statut2label[$statut] : "default";
    $text = '';
    if($secondPassage) {
        $text .= '<span class="label label-danger" style="margin-right: -14px;">&nbsp;&nbsp;</span>';
    }
    $text .= '<span';
    if($secondPassage) {
        $text .= ' style="border-radius: 0 0.25em 0.25em 0; border-left: 1px solid #fff;"';
    }
    if($detail) {
        $text .= ' style="border-radius: 0.25em 0 0  0.25em;"';
    }
    $text .= ' class="label label-';
    $text .= $labelClass;
    $text .= '">';
    if (isset(Lot::$libellesStatuts[$statut])) {
        $text .= Lot::$libellesStatuts[$statut];
    }
    $text .= '</span>';
    if($detail) {
        $text .= "<span data-toggle=\"tooltip\" data-html=\"true\" title=\"$detail\" style='border-radius: 0 0.25em 0.25em 0; border-left: 1px solid #fff;' class='label label-".$labelClass."'>".$detail."</span>";
    }
    return $text;
}

function substrUtf8($str, $offset, $length) {
  return utf8_encode(substr(utf8_decode($str), $offset, $length));
}

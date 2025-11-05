<?php

function showOnlyProduit($lot, $show_always_specificite = true, $tag = 'small')
{

  $text = $lot->produit_libelle." ";
  if($tag) {
      $text .= "<".$tag.">";
  }
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

  if($tag) {
      $text .= "</".$tag.">";
  }
  return trim($text);
}

function showProduitCepagesLot($lot, $show_always_specificite = true, $tagSmall = 'small')
{
    $text = "";
    $text .= showOnlyProduit($lot, $show_always_specificite, $tagSmall);
    $text .= showOnlyCepages($lot, null, $tagSmall);
    return $text;
}

function showOnlyCepages($lot, $maxcars = null, $tag = 'small') {
  $text = '';
  $html = "";
  if($tag) {
      $html = " <".$tag." class='text-muted'>";
  }
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
  if (!$text && $tag) {
    return " <".$tag.">&nbsp;</".$tag.">";
  }
  if ($maxcars) {
      $text = substrUtf8($text, 0, $maxcars);
  }
  $html .= $text;
  if($tag) {
      $html .= "</".$tag.">";
  }
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
            return url_for('degustation_nonconformites');

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
    $lotOrigine = $lot->getLotInDrevOrigine();

    if(!$lotOrigine) {
        throw new Exception("Lot ".$lot->getDocument()->_id.":".$lot->getHash()." non trouvé");
    }

    if($lotOrigine->id_document_affectation && $lotOrigine->date_commission <= date("Y-m-d")) {
        return '<span title="Dégusté" class="glyphicon glyphicon-ok-circle text-success"></span>';
    }

    if($lotOrigine->affectable) {
        return '<span title="À déguster" class="glyphicon glyphicon-time text-success"></span>';
    }

    return '<span title="Réputé conforme" style="opacity: 0.5;" class="text-muted glyphicon glyphicon-ok"></span>';
}

function showLotStatusCartouche($lot_ou_mvt_value, $with_details = true) {
    $statut = $lot_ou_mvt_value->statut;
    $detail = null;
    if(isset($lot_ou_mvt_value->detail)) {
        $detail = $lot_ou_mvt_value->detail;
    }
    if (!isset(Lot::$libellesStatuts[$statut]) && !$detail) {
        return ;
    }
    $secondPassage = isset($lot_ou_mvt_value->libelle) && preg_match("/ème dégustation/", $lot_ou_mvt_value->libelle);
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
    if($detail && $with_details) {
        $text .= "<span data-toggle=\"tooltip\" data-html=\"true\" title=\"$detail\" style='border-radius: 0 0.25em 0.25em 0; border-left: 1px solid #fff;' class='label label-".$labelClass."'>".$detail."</span>";
    }
    if (isset($lot_ou_mvt_value->region) && $lot_ou_mvt_value->region === "AOCGAILLAC") {
        $text = str_ireplace("conform", "Acceptabl", $text);
    }
    return $text;
}

function showLotPublicStatusCartouche($mvt_value, $with_details = true) {
    if (MouvementLotHistoryView::isWaitingLotNotification($mvt_value)) {
        return "<span data-toggle=\"tooltip\" data-html=\"true\" style='border-radius: 0 0.25em 0.25em 0; border-left: 1px solid #fff;' class='label label-default'>En attente de contrôle</span>";
    }
    if (strpos($mvt_value->detail, ' anon') !== false) {
        $mvt_value->detail = '';
    }
    return showLotStatusCartouche($mvt_value, $with_details);
}
function showSummerizedLotPublicStatusCartouche($mvt_value) {
    if (MouvementLotHistoryView::isWaitingLotNotification($mvt_value)) {
        return "<span data-toggle=\"tooltip\" data-html=\"true\" title=\"L'opérateur voit ici EN ATTENTE DE CONTROLE, la notification n'ayant pas été envoyée. L'accès à l'historique ne leur est pas permis.\" style='border-radius: 0 0.25em 0.25em 0; border-left: 1px solid #fff;' class='label label-default'><span class='glyphicon glyphicon-eye-close'></span></span>";
    }
}

function substrUtf8($str, $offset, $length) {
  return utf8_encode(substr(utf8_decode($str), $offset, $length));
}

function clarifieTypeDocumentLibelle($type) {
    $result = str_replace('Transaction', 'VracExport', $type);
    $result = str_replace('TRANSACTION', 'VRAC_EXPORT', $result);
    return $result;
}

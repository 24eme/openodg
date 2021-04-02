<?php

function showProduitLot($lot, $specificite_protection = true)
{

  $text = $lot->produit_libelle." <small>";
  $text .= ($lot->millesime) ? $lot->millesime : "";
  if (!$specificite_protection || DrevConfiguration::getInstance()->hasSpecificiteLot()) {
      if($lot->specificite && $lot->specificite !== Lot::SPECIFICITE_UNDEFINED){
          $text .= ' - '.$lot->specificite;
      }
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
  if ($lot instanceof stdClass) {
    $total = $lot->volume;
    $text .= " <small class='text-muted'>";
    foreach ($lot->cepages as $cepage => $hl) {
        $text .= $cepage . ' ' . round(($hl*100)/$total, 2) . "%";
    }
    $text .= "</small>";
  } else {
    if ($lot->cepages) {
      $text .= " <small class='text-muted'>".$lot->getCepagesLibelle()."</small>";
    }
    if($lot->exist("details")) {
        $text .= " <small class='text-muted'>".$lot->details."</small>";
    }
  }
    return $text;
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
            return url_for('degustation_prelevement_lots', ['id' => $documentId]);
    }
    return url_for(strtolower($mvtLot->value->document_type).'_visualisation', array('id' => $documentId));
}

function pictoDegustable($lot) {
    if($lot->id_document_affectation) {
        return '<span title="Dégusté" class="glyphicon glyphicon-ok text-success"></span>';
    }

    if($lot->affectable) {
        return '<span title="À déguster" class="glyphicon glyphicon-time"></span>';
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
        $text .= "<span style='border-radius: 0 0.25em 0.25em 0; border-left: 1px solid #fff;' class='label label-".$labelClass."'>".$detail."</span>";
    }
    return $text;
}

function splitLogementAdresse($adresseLogement){
    if(!$adresseLogement){
        return $adresseLogement;
    }

    $adresseSplit = explode('—',$adresseLogement);
    $adresse['nom'] = trim($adresseSplit[0]);
    $adresse['adresse'] = trim($adresseSplit[1]);
    $adresse['code_postal'] = trim($adresseSplit[2]);
    $adresse['commune'] = trim($adresseSplit[3]);

    return $adresse;
}

<?php

function getHeurePlus($prelevement, $heureplus) {
    return substr($prelevement->heure, 0, 2) + $heureplus . ":" . substr($prelevement->heure, 3, 2);
}

function getAdresseChai($prelevement) {
    return $prelevement->adresse . " " . $prelevement->code_postal . " " . $prelevement->commune;
}

function getDatesPrelevements($degustation) {
    setlocale(LC_ALL, 'fr_FR');
    return 'Du ' . format_date($degustation->date_prelevement_debut, "D", "fr_FR") . ' au ' . format_date($degustation->date_prelevement_fin, "D", "fr_FR");
}

function getTypeCourrier($prelevement) {
    if (!$prelevement->exist('type_courrier') || !$prelevement->type_courrier) {
        return "<span class='glyphicon glyphicon-ban-circle' ></span>";
    }
    $type_courrier_libelle = DegustationClient::$types_courrier_libelle[$prelevement->type_courrier];
    if ($prelevement->type_courrier == DegustationClient::COURRIER_TYPE_VISITE) {
        $type_courrier_libelle .= '<br /><small>' . ucfirst(format_date($prelevement->visite_date, "P", "fr_FR")) . ' à ' . $prelevement->visite_heure.'</small>';
    }
    return "<span class='glyphicon glyphicon-file'></span>&nbsp;".$type_courrier_libelle;
}

function getLibelleTypeNote($type_note) {
    switch ($type_note) {
        case DegustationClient::NOTE_TYPE_QUALITE_TECHNIQUE:
            return "Note en qualité technique";
        case DegustationClient::NOTE_TYPE_MATIERE:
            return "Note en matière";
        case DegustationClient::NOTE_TYPE_TYPICITE:
            return "Note en typicité";
        case DegustationClient::NOTE_TYPE_CONCENTRATION:
            return "Note en concentration";
        case DegustationClient::NOTE_TYPE_EQUILIBRE:
            return "Note en équilibre";
        default:
            break;
    }
}

function getExplicationsPDF($prelevement) {
    switch ($prelevement->type_courrier) {
        case DegustationClient::COURRIER_TYPE_OK:
            return "";
        case DegustationClient::COURRIER_TYPE_OPE:
            return "<p style=\"text-align: justify;\">Nos experts ont noté des observations sur votre vin. N'hésitez pas à nous solliciter si vous avez besoin d'un appui technique suite à ces remarques ou simplement pour vous apporter un éclairage sur le cahier des charges.</p>";
        case DegustationClient::COURRIER_TYPE_VISITE: {
                $heurePlus = (int) format_date($prelevement->visite_heure, "H", "fr_FR") + 2;
                return "<p style=\"text-align: justify;\">Afin de discuter avec vous des remarques attribuées à votre échantillon, nous vous proposons de vous rencontrer <strong>à votre chai</strong> :<br />
<span style=\"text-align: center;\"><strong>Le " . format_date($prelevement->visite_date, "P", "fr_FR") . " entre " . format_date($prelevement->visite_heure, "H", "fr_FR") . "h et " . $heurePlus . "h</strong></span></p>
<p style=\"text-align: justify;\">En cas d'empêchement, merci de nous le faire savoir au ".sfConfig::get('app_degustation_courrier_visitetel')." (".sfConfig::get('app_degustation_courrier_visiteorga').").</p>";
            }
        default:
            break;
    }
}

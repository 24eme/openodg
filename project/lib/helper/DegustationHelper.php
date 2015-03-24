<?php

function getHeurePlus($prelevement,$heureplus) {
   return substr($prelevement->heure,0,2)+$heureplus . ":".  substr($prelevement->heure, 3,2);
}

function getAdresseChai($prelevement){
    return $prelevement->adresse ." ". $prelevement->code_postal . " " .$prelevement->commune;
}

function getDatesPrelevements($degustation){
    setlocale(LC_ALL, 'fr_FR');
    return 'Du '.format_date($degustation->date_prelevement_debut, "D", "fr_FR").' au '.format_date($degustation->date_prelevement_fin, "D", "fr_FR");
}

function getTypeCourrier($prelevement){
    if(!$prelevement->exist('type_courrier') || ! $prelevement->type_courrier){
        return "<span class='glyphicon glyphicon-ban-circle' ></span>";
    }
    return DegustationClient::$types_courrier_libelle[$prelevement->type_courrier];
}
<?php

function getHeurePlus($prelevement,$heureplus) {
   return substr($prelevement->heure,0,2)+$heureplus . ":".  substr($prelevement->heure, 3,2);
}

function getAdresseChai($prelevement){
    return $prelevement->adresse ." ". $prelevement->code_postal . " " .$prelevement->commune;
}
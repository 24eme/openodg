<?php
function getDatesDistillation($drevmarc){
    setlocale(LC_ALL, 'fr_FR');
    return 'Du '.format_date($drevmarc->debut_distillation, "D", "fr_FR").' au '.format_date($drevmarc->fin_distillation, "D", "fr_FR");
}

function getQtemarc($drevmarc) {
   return $drevmarc->qte_marc .'&nbsp;kg';
}


function getVolumeObtenu($drevmarc) {
    
return $drevmarc->volume_obtenu . '&nbsp;hl d\'alcool pur';
}

function getTitreAlcoolVol($drevmarc) {
    
return $drevmarc->titre_alcool_vol . '&nbsp;°';
}

function getErrorClass($fieldError,&$hasError){
    if($hasError === true){
        return "";
    }else{
        if($fieldError != ""){
            $hasError = true;
            return "error_field_to_focused";
        }
    }
}


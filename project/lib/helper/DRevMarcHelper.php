<?php
function getDatesDistillation($drevmarc){
    setlocale(LC_ALL, 'fr_FR');
    return 'Du '.format_date($drevmarc->debut_distillation, "D", "fr_FR").' au '.format_date($drevmarc->fin_distillation, "D", "fr_FR");
}

function getQtemarc($drevmarc) {
   return $drevmarc->qte_marc .'&nbsp;<small class="text-muted">kg</small>';
}


function getVolumeObtenu($drevmarc) {
    
return $drevmarc->volume_obtenu . '&nbsp;<small class="text-muted">hl d\'alcool pur</small>';
}

function getTitreAlcoolVol($drevmarc) {
    
return $drevmarc->titre_alcool_vol . '&nbsp;<small class="text-muted">°</small>';
}


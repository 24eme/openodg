<?php

function getPointAideText($categorie, $idPointAide, $default_msg = '') {
    return PointsAidesConfiguration::getInstance()->getPointAide($categorie, $idPointAide, $default_msg);
}

function getPointAideHtml($categorie, $idPointAide, $default_msg = '') {
    $text = getPointAideText($categorie, $idPointAide, $default_msg);
    $hmtl = '&nbsp;<span class="glyphicon glyphicon-question-sign" style="cursor:pointer;" data-toggle="tooltip" title="'.$text.'"></span>';
    return $hmtl;
}

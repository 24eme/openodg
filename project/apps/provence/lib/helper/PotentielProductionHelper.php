<?php

function echoAppellation($appellation) {
    $result = '';
    switch ($appellation) {
        case 'FRE':
            $result = 'Fréjus';
            break;
        case 'LLO':
            $result = 'La Londe';
            break;
        case 'SVI':
            $result = 'Sainte Victoire';
            break;
        case 'PIE':
            $result = 'Pierrefeu';
            break;
        case 'NDA':
            $result = 'Notre-Dame des Anges';
            break;
        default:
           $result = 'GENERIQUE';
    }
    echo $result;
}

function echoThRevendicable($type, $cat) {
    $result = '';
    switch ($cat) {
        case 'principaux':
            $result = ($type == 'revendicable')? 'Cépages principaux revendicables' : 'Cépages principaux déclassés';
            break;
        case 'secondaires':
            $result = ($type == 'revendicable')? 'Cépages secondaires revendicables' : 'Cépages secondaires déclassés';
            break;
        case 'secondairesNoirs':
            $result = ($type == 'revendicable')? 'Cépages secondaires noirs revendicables' : 'Cépages secondaires noirs déclassés';
            break;
        case 'secondairesBlancsVermentino':
            $result = ($type == 'revendicable')? 'Cépage Vermentino revendicable en Rg/Rs' : 'Cépage Vermentino revendicable en Blc uniquement';
            break;
        case 'secondairesBlancsAutres':
            $result = ($type == 'revendicable')? 'Cépages Clairette, Semillon, Ugni revendicables en Rg/Rs' : 'Cépages Clairette, Semillon, Ugni revendicables en Blc uniquement';
            break;
        default:
            $result = '';
    }
    echo $result;
}
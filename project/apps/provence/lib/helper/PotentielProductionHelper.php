<?php

function echoAppellation($appellation) {
    $result = '';
    switch ($appellation) {
        case 'FRE':
            $result = 'Côtes de Provence Fréjus';
            break;
        case 'LLO':
            $result = 'Côtes de Provence La Londe';
            break;
        case 'SVI':
            $result = 'Côtes de Provence Sainte Victoire';
            break;
        case 'PIE':
            $result = 'Côtes de Provence Pierrefeu';
            break;
        case 'NDA':
            $result = 'Côtes de Provence Notre-Dame des Anges';
            break;
        default:
           $result = 'Côtes de Provence GENERIQUE';
    }
    echo $result;
}


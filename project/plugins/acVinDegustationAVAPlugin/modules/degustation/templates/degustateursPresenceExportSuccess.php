<?php
use_helper('Csv');

printf("\xef\xbb\xbf");//UTF8 BOM (pour windows)
echo "#type de dégustation;date de la dégustation;collége;nom;adresse;code_postal;commune;email;présence;compte_id;tournee_id;\n";

foreach($tournee->degustateurs as $college => $degustateurs) {
    foreach($degustateurs as $compte_id => $degustateur) {
        $degustateurEscape = $degustateur->getRawValue();
        echo '"' . escapeCSVValue($tournee->appellation) . '";';
        echo '"' . escapeCSVValue($tournee->date) . '";';
        echo '"' . escapeCSVValue(CompteClient::getInstance()->getAttributLibelle($college)) . '";';
        echo '"' . escapeCSVValue($degustateurEscape->nom) . '";';
        echo '"' . escapeCSVValue($degustateurEscape->adresse) . '";';
        echo '"' . escapeCSVValue($degustateurEscape->code_postal) . '";';
        echo '"' . escapeCSVValue($degustateurEscape->commune) . '";';
        echo '"' . escapeCSVValue($degustateurEscape->email) . '";';
        if($degustateurEscape->presence === 0) {
            echo '"Non présent";';
        } elseif ($degustateurEscape->presence === null) {
            echo '"Ne sais pas";';
        } elseif ($degustateurEscape->presence === 1) {
            echo '"Présent";';
        } else {
            echo ';';
        }
        echo '"' . escapeCSVValue($compte_id) . '";';
        echo '"' . escapeCSVValue($tournee->_id) . '";';
        echo "\n";
    }
}

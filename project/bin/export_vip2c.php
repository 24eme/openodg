<?php

if ($argc !== 3) {
    die('Missing parameters'.PHP_EOL);
}

$file_drev_lots = $argv[1];
$file_lots      = $argv[2];

$drev_lots = fopen($file_drev_lots, 'r');
$lots = fopen($file_lots, 'r');

$operateurs = [];

while (($line = fgetcsv($drev_lots, 1000, ';')) !== false) {
    if (! (strpos($line[47], '/MED/') !== false && strpos($line[47], '/rose/') !== false)) {
        continue;
    }

    if ($line[1] !== "2022-2023") {
        continue;
    }

    if (array_key_exists($line[4], $operateurs) === false) {
        $operateurs[$line[4]]['organisme'] = $line[44];
        $operateurs[$line[4]]['cvi'] = $line[4];
        $operateurs[$line[4]]['volume_revendique'] = 0;
        $operateurs[$line[4]]['volume_commercialise'] = 0;
        $operateurs[$line[4]]['vip2c'] = 0;
    }

    $operateurs[$line[4]]['volume_revendique'] += round(str_replace(',', '.', $line[27]), 2);
}

while (($line = fgetcsv($lots, 1000, ';')) !== false) {
    if ($line[6] !== "2022-2023") {
        continue;
    }

    if (! (strpos($line[36], '/MED/') !== false && strpos($line[36], '/rose/') !== false)) {
        continue;
    }

    /* if (in_array(utf8_encode($line[0]), ['DRev', 'DRev:Changé']) === false) { */
    /*     continue; */
    /* } */

    if ($line[0] === "Conditionnement") {
        continue;
    }

    if (in_array(utf8_encode($line[24]), ['Réputé conforme', 'Conforme', 'Conforme en appel']) === false) {
        continue;
    }

    if (array_key_exists($line[1], $operateurs) === false) {
        $operateurs[$line[1]]['organisme'] = $line[33];
        $operateurs[$line[1]]['cvi'] = $line[1];
        $operateurs[$line[1]]['volume_revendique'] = 0;
        $operateurs[$line[1]]['volume_commercialise'] = 0;
        $operateurs[$line[1]]['vip2c'] = 0;
    }

    $operateurs[$line[1]]['volume_commercialise'] += round(str_replace(',', '.', $line[23]), 2);
}

fclose($drev_lots);
fclose($lots);

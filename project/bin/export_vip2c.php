<?php

if ($argc !== 3) {
    die('Missing parameters');
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

    if (array_key_exists($line[2], $operateurs) === false) {
        $operateurs[$line[2]]['organisme'] = $line[44];
        $operateurs[$line[2]]['cvi'] = $line[4];
        $operateurs[$line[2]]['volume_revendique'] = 0;
        $operateurs[$line[2]]['volume_commercialise'] = 0;
        $operateurs[$line[2]]['vip2c'] = 0;
    }

    $operateurs[$line[2]]['volume_revendique'] += round($line[27], 2);
}

fclose($drev_lots);
fclose($lots);

var_dump($operateurs['BDR00077501']);

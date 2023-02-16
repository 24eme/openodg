<?php

if ($argc !== 4) {
    die('Missing parameters'.PHP_EOL);
}

$file_drev_lots = $argv[1];
$file_lots      = $argv[2];
$file_etablissement = $argv[3];

$drev_lots = fopen($file_drev_lots, 'r');
$lots = fopen($file_lots, 'r');
$etablissements = file($file_etablissement);
$vip2c = file('data/configuration/VIP2C2022.csv');

// Récup VIP2C
array_walk($vip2c, function (&$item, $key) {
    $item = str_getcsv($item, ',');
});
$cvis = array_column($vip2c, 3);
$vip2c = array_combine($cvis, $vip2c);

// Récup CVI
array_walk($etablissements, function (&$item, $key) {
    $item = str_getcsv($item, ';');
});
$ids = array_column($etablissements, 32);
$etablissements = array_combine($ids, $etablissements);

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
        $operateurs[$line[2]]['identifiant'] = $line[2];
        $operateurs[$line[2]]['cvi'] = $line[4];
        $operateurs[$line[2]]['volume_revendique'] = 0;
        $operateurs[$line[2]]['date_last_revendique'] = $line[13];
        $operateurs[$line[2]]['volume_commercialise'] = 0;
        $operateurs[$line[2]]['date_last_commercialise'] = null;
        $operateurs[$line[2]]['vip2c'] = 0;
    }

    $operateurs[$line[2]]['volume_revendique'] += round(str_replace(',', '.', $line[27]), 2);

    $date_lot             = DateTimeImmutable::createFromFormat('Y-m-d', $line[13]);
    $date_last_revendique = DateTimeImmutable::createFromFormat('Y-m-d', $operateurs[$line[2]]['date_last_revendique']);

    if ($date_lot > $date_last_revendique) {
        $operateurs[$line[2]]['date_last_revendique'] = $date_lot->format('Y-m-d');
    }
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
        $operateurs[$line[1]]['identifiant'] = $line[1];
        $operateurs[$line[1]]['cvi'] = null;
        $operateurs[$line[1]]['volume_revendique'] = 0;
        $operateurs[$line[1]]['date_last_revendique'] = null;
        $operateurs[$line[1]]['volume_commercialise'] = 0;
        $operateurs[$line[1]]['date_last_commercialise'] = $line[8];
        $operateurs[$line[1]]['vip2c'] = 0;
    }

    $operateurs[$line[1]]['volume_commercialise'] += round(str_replace(',', '.', $line[23]), 2);

    $date_lot                = DateTimeImmutable::createFromFormat('Y-m-d', $line[8]);
    $date_last_commercialise = DateTimeImmutable::createFromFormat('Y-m-d', $operateurs[$line[1]]['date_last_commercialise']);

    if ($operateurs[$line[1]]['date_last_commercialise'] === null || $date_lot > $date_last_commercialise) {
        $operateurs[$line[1]]['date_last_commercialise'] = $date_lot->format('Y-m-d');
    }
}

foreach ($operateurs as $id => &$operateur) {
    if (! $operateur['cvi']) {
        $operateur['cvi'] = $etablissements[$id][8];
    }

    if (array_key_exists($operateur['cvi'], $vip2c)) {
        $operateur['vip2c'] += (int) str_replace(',', '', trim($vip2c[$operateur['cvi']][11]));
    }
}

fclose($drev_lots);
fclose($lots);

$operateurs = array_filter($operateurs, function ($item) {
    return $item['vip2c'] > 0;
});

array_walk($operateurs, function (&$operateur, $key) {
    $d = [];

    if ($operateur['vip2c'] < $operateur['volume_revendique']) {
        $d[] = 'DREV';
    }

    if ($operateur['vip2c'] < $operateur['volume_commercialise']) {
        $d[] = 'COMM';
    }

    $operateur['depassement'] = implode('+', $d);
});

$f = fopen('php://output', "w");
fputcsv($f, ['Organisme', 'Identifiant', 'CVI', 'Revendiqué', 'Commercialisé', 'VIP2C', 'Dépassement'], ';');
foreach ($operateurs as $operateur) {
    fputcsv($f, $operateur, ';');
}
fclose($f);

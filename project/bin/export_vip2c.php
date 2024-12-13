<?php

function getHashProduit($line, $startAt) {
    $cert = getHashValue($line[$startAt]);
    $gen = getHashValue($line[$startAt+1]);
    $app = getHashValue($line[$startAt+2]);
    $men = getHashValue($line[$startAt+3]);
    $lieu = getHashValue($line[$startAt+4]);
    $coul = getHashValue($line[$startAt+5]);
    $cep = getHashValue($line[$startAt+6]);
    return "certifications/$cert/genres/$gen/appellations/$app/mentions/$men/lieux+couleurs/$coul/cepages/$cep";
}
function getHashValue($value) {
    return (trim($value))? trim($value) : 'DEFAUT';
}

if ($argc !== 4) {
    die('Missing parameters. Usage: '.$argv[0].' drev_lots.csv lots.csv etablissements.csv'.PHP_EOL);
}

$file_drev_lots = $argv[1];
$file_lots      = $argv[2];
$file_etablissement = $argv[3];

$drev_lots = fopen($file_drev_lots, 'r');
$lots = fopen($file_lots, 'r');
$etablissements = file($file_etablissement);
$vip2c = file('data/configuration/VIP2C.csv');

// Récup VIP2C
array_walk($vip2c, function (&$item, $key) {
    $item = str_getcsv($item, ',');
});
$cvis = array_column($vip2c, 3);
$millesimes = array_unique(array_column($vip2c, 0));
$produits = array_unique(array_column($vip2c, 6));
$result = [];
foreach($vip2c as $values) {
    $result[$values[3].'_'.$values[0].'_'.$values[6]] = $values;
}
$vip2c = $result;

// Récup CVI
array_walk($etablissements, function (&$item, $key) {
    $item = str_getcsv($item, ';');
});
$ids = array_column($etablissements, 32);
$etablissements = array_combine($ids, $etablissements);

$operateurs = [];

while (($line = fgetcsv($drev_lots, 1000, ';')) !== false) {
    if (!isset($line[47])) {
        print_r(['ERROR', $line]);
    }
    $hash = getHashProduit($line, 16);
    if (!in_array($hash, $produits)) {
        continue;
    }

    if (!in_array($line[25], $millesimes)) {
        continue;
    }

    $key = $line[2].'_'.$line[25].'_'.$hash;

    if (array_key_exists($key, $operateurs) === false) {
        $operateurs[$key]['organisme'] = $line[44];
        $operateurs[$key]['identifiant'] = $line[2];
        $operateurs[$key]['cvi'] = $line[4];
        $operateurs[$key]['millesime'] = $line[25];
        $operateurs[$key]['produit'] = utf8_encode($line[23]);
        $operateurs[$key]['hash'] = $hash;
        $operateurs[$key]['volume_revendique'] = 0;
        $operateurs[$key]['date_last_revendique'] = $line[13];
        $operateurs[$key]['volume_commercialise'] = 0;
        $operateurs[$key]['date_last_commercialise'] = null;
        $operateurs[$key]['vip2c'] = 0;
    }

    $operateurs[$key]['volume_revendique'] += round(str_replace(',', '.', $line[27]), 2);

    $date_lot             = DateTimeImmutable::createFromFormat('Y-m-d', $line[13]);
    $date_last_revendique = DateTimeImmutable::createFromFormat('Y-m-d', $operateurs[$key]['date_last_revendique']);

    if ($date_lot > $date_last_revendique) {
        $operateurs[$key]['date_last_revendique'] = $date_lot->format('Y-m-d');
    }
}

while (($line = fgetcsv($lots, 1000, ';')) !== false) {

    $hash = getHashProduit($line, 12);
    if (!in_array($hash, $produits)) {
        continue;
    }

    if (!in_array($line[21], $millesimes)) {
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

    $key = $line[1].'_'.$line[21].'_'.$hash;

    if (array_key_exists($key, $operateurs) === false) {
        $operateurs[$key]['organisme'] = $line[33];
        $operateurs[$key]['identifiant'] = $line[1];
        $operateurs[$key]['cvi'] = null;
        $operateurs[$key]['millesime'] = $line[21];
        $operateurs[$key]['produit'] = utf8_encode($line[19]);
        $operateurs[$key]['hash'] = $hash;
        $operateurs[$key]['volume_revendique'] = 0;
        $operateurs[$key]['date_last_revendique'] = null;
        $operateurs[$key]['volume_commercialise'] = 0;
        $operateurs[$key]['date_last_commercialise'] = $line[8];
        $operateurs[$key]['vip2c'] = 0;
    }

    $operateurs[$key]['volume_commercialise'] += round(str_replace(',', '.', $line[23]), 2);

    $date_lot                = DateTimeImmutable::createFromFormat('Y-m-d', $line[8]);
    $date_last_commercialise = DateTimeImmutable::createFromFormat('Y-m-d', $operateurs[$key]['date_last_commercialise']);

    if ($operateurs[$key]['date_last_commercialise'] === null || $date_lot > $date_last_commercialise) {
        $operateurs[$key]['date_last_commercialise'] = $date_lot->format('Y-m-d');
    }
}

foreach ($operateurs as $id => &$operateur) {
    if (! $operateur['cvi']) {
        $operateur['cvi'] = $etablissements[substr($id, 0, strpos($id, '_'))][8];
    }

    if (array_key_exists($operateur['cvi'].'_'.$operateur['millesime'].'_'.$operateur['hash'], $vip2c)) {
        $operateur['vip2c'] += (int) str_replace(',', '', trim($vip2c[$operateur['cvi'].'_'.$operateur['millesime'].'_'.$operateur['hash']][7]));
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

    $operateur['lot_plus_recent'] = (DateTimeImmutable::createFromFormat('Y-m-d', $operateur['date_last_revendique']) > DateTimeImmutable::createFromFormat('Y-m-d', $operateur['date_last_commercialise']))
                                    ? $operateur['date_last_revendique']
                                    : $operateur['date_last_commercialise'];
});

$f = fopen('php://output', "w");
fputcsv($f, ['Organisme', 'Identifiant', 'CVI', 'Millésime', 'Produit', 'Revendiqué', 'Date dernière revendication', 'Commercialisé', 'Date dernière commercialisation', 'VIP2C', 'Dépassement', 'Lot le plus récent'], ';');
foreach ($operateurs as $operateur) {
    unset($operateur['hash']);
    fputcsv($f, $operateur, ';');
}
fclose($f);

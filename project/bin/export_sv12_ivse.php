<?php

$exportDir = 'web/exports_igp/';
$sv12CsvFile = $exportDir.'sv12.csv';
$inaoIVSE  = [
    "004",
    "007",
    "033",
    "034",
    "041",
    "051",
    "061",
    "071",
    "072",
    "073",
    "130",
    "131",
    "133",
    "261",
    "262",
    "263",
    "264",
    "381",
    "832",
    "833",
    "834",
    "835",
    "836",
    "841",
    "842",
    "843"
];

$handle = fopen($sv12CsvFile,'r');
$csv = null;
while (($data = fgetcsv($handle, null, ';')) !== false ) {
    if (!$csv) {
        $csv = implode(';', $data);
    }
    $inao = trim($data[16]);
    if ($inao[0] != 3) continue;
    if (!in_array(substr($inao, 2, 3), $inaoIVSE)) continue;
    $csv .= PHP_EOL.implode(';', $data);
}

echo (file_put_contents($exportDir.'sv12_ivse.csv', $csv) === false)?  "\033[31m Export failed\n" : "\033[32m ".$exportDir.'sv12_ivse.csv'.' created successfully\n';

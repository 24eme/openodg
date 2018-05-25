<?php require_once(dirname(__FILE__).'/../bootstrap/common.php');


$csv = new SV11DouaneCsvFile(dirname(__FILE__).'/../data/sv11_douane.csv');
$csvConvert = $csv->convert();

$lines = explode("\n", $csvConvert);

$linesAObtenir = array(
     array('produit' => '1B525', 'produit_libelle' => 'CONDRIEU', 'values' => array(2700, 0.4579, 19.42, 19.42)),
     array('produit' => '1B541', 'produit_libelle' => 'Hermitage ou Ermitage bl', 'values' => array(850,0.1484,6.75,6.75)),
     array('produit' => '1R542', 'produit_libelle' => 'Crozes-Hermitage rg', 'values' => array(7000, 1.08, 54, 54)),
     array('produit' => '1R526', 'produit_libelle' => 'CORNAS', 'values' => array(3000, 0.5495, 23, 23)),
     array('produit' => '1B542', 'produit_libelle' => 'Crozes-Hermitage bl', 'values' => array(5000, 0.8866, 30.96, 30.96)),
     array('produit' => '1R542', 'produit_libelle' => 'Crozes-Hermitage rg', 'values' => array(5528, 0.72, 37.14, 37.14)),
     array('produit' => '1B542', 'produit_libelle' => 'Crozes-Hermitage bl', 'values' => array(800, 0.255, 5.04, 5.04)),
     array('produit' => '1R542', 'produit_libelle' => 'Crozes-Hermitage rg', 'values' => array(15454, 2.2352, 112.86, 112.86)),
     array('produit' => '1R542', 'produit_libelle' => 'Crozes-Hermitage rg', 'values' => array(2000, 0.3234, 16, 16)),
     array('produit' => '1R542', 'produit_libelle' => 'Crozes-Hermitage rg', 'values' => array(2082, 0.312, 15, 15)),
);

$t = new lime_test((count($lines) - 1) * 5);

$i = 0;
foreach($lines as $line) {
    if(!$line) {
        continue;
    }

    $line = explode(";", $line);
    $t->is($line[SV11CsvFile::CSV_TYPE], "SV11", "Le type de la ligne est SV11");
    $t->is($line[SV11CsvFile::CSV_CAMPAGNE], date('Y'), "La campagne est 2017");
    $t->is($line[SV11CsvFile::CSV_RECOLTANT_CVI], "7523700100", "Le CVI est 7523700100");
    $t->is($line[SV11CsvFile::CSV_RECOLTANT_LIBELLE], "\"ACTUALYS JEAN\"", "Le nom est ACTUALYS JEAN");
    $t->is($line[SV11CsvFile::CSV_RECOLTANT_COMMUNE], "NEUILLY", "Le commune est NEUILLY");

    $i++;
}

<?php require_once(dirname(__FILE__).'/../bootstrap/common.php');


$csv = new SV11DouaneCsvFile(dirname(__FILE__).'/../data/sv11_douane_'.$application.'.csv');
$csvConvert = $csv->convert();

$lines = explode("\n", $csvConvert);

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

<?php require_once(dirname(__FILE__).'/../bootstrap/common.php');

$csv = new DRDouaneCsvFile(dirname(__FILE__).'/../data/dr_douane.csv');
$csvExploitant = $csv->convert();
$csvBailleur = $csv->convert();
$linesExploitant = explode("\n", $csvExploitant);
$linesBailleur = explode("\n", $csvBailleur);

$t = new lime_test(((count($linesExploitant) - 1) * 5) + ((count($linesBailleur) - 1)) * 5);

$t->diag("Tests sur les données Exploitants");

$i = 0;
foreach($linesExploitant as $line) {
    if(!$line) {
        continue;
    }

    $line = explode(";", $line);

    $t->is($line[DRCsvFile::CSV_TYPE], "DR", "Le type de la ligne est DR");
    $t->is($line[DRCsvFile::CSV_CAMPAGNE], date('Y'), "La campagne est 2017");
    $t->is($line[DRCsvFile::CSV_RECOLTANT_CVI], "7523700100", "Le CVI est 7523700100");
    $t->is($line[DRCsvFile::CSV_RECOLTANT_LIBELLE], "\"ACTUALYS JEAN\"", "Le nom est ACTUALYS JEAN");
    $t->is($line[DRCsvFile::CSV_RECOLTANT_COMMUNE], "NEUILLY", "Le commune est NEUILLY");

    $i++;
}


$t->diag("Tests sur les données Bailleur");

foreach($linesBailleur as $line) {
    if(!$line) {
        continue;
    }

    $line = explode(";", $line);

    $t->is($line[DRCsvFile::CSV_TYPE], "DR", "Le type de la ligne est DR");
    $t->is($line[DRCsvFile::CSV_CAMPAGNE], date('Y'), "La campagne est 2017");
    $t->is($line[DRCsvFile::CSV_RECOLTANT_CVI], "7523700100", "Le CVI est 7523700100");
    $t->is($line[DRCsvFile::CSV_RECOLTANT_LIBELLE], "\"ACTUALYS JEAN\"", "Le nom est ACTUALYS JEAN");
    $t->is($line[DRCsvFile::CSV_RECOLTANT_COMMUNE], "NEUILLY", "Le commune est NEUILLY");

    $i++;
}

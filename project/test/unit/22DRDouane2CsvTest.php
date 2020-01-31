<?php require_once(dirname(__FILE__).'/../bootstrap/common.php');

$csv = new DRDouaneCsvFile(dirname(__FILE__).'/../data/dr_douane_'.$application.'.csv');
$csvExploitant = $csv->convert();
$csvBailleur = $csv->convert();
$linesExploitant = explode("\n", $csvExploitant);
$linesBailleur = explode("\n", $csvBailleur);

$t = new lime_test(((count($linesExploitant) - 1) * 6) + ((count($linesBailleur) - 1)) * 5);

$certifications = array('loire' => 'AOC_INTERLOIRE', 'rhone' => 'AOP', 'provence' => 'AOP');

$t->comment("Fichier de test : ".dirname(__FILE__).'/../data/dr_douane_'.$application.'.csv');
$t->diag("Tests sur les données Exploitants");

$i = 0;
$last_l = 9999;
foreach($linesExploitant as $line) {
    if(!$line) {
        continue;
    }

    $line = explode(";", $line);

    $identifiant_colonne = $line[DRCsvFile::CSV_PRODUIT_INAO].$line[DRCsvFile::CSV_PRODUIT_LIBELLE].$line[DRCsvFile::CSV_PRODUIT_COMPLEMENT];
    if ($last_i != $identifiant_colonne) {
        $colonneid ++;
    }
    $last_i = $identifiant_colonne;

    if ($line[DRCsvFile::CSV_COLONNE_ID] == '9999') {
        $t->is($line[DRCsvFile::CSV_TYPE], "DR", "Le type de la ligne est DR");
        $t->is($line[DRCsvFile::CSV_CAMPAGNE], date('Y'), "La campagne est 2017");
        $t->is($line[DRCsvFile::CSV_RECOLTANT_CVI], "7523700100", "Le CVI est 7523700100");
        $t->is($line[DRCsvFile::CSV_RECOLTANT_LIBELLE], "\"ACTUALYS JEAN\"", "Le nom est ACTUALYS JEAN");
        $t->is($line[DRCsvFile::CSV_RECOLTANT_COMMUNE], "NEUILLY", "Le commune est NEUILLY");
        $t->ok(preg_match('/^[0-9]+$/', $line[DRCsvFile::CSV_TIERS_CVI]), "CVI de l'Acheteur : ".$line[DRCsvFile::CSV_TIERS_CVI]);
        $t->ok(preg_match('/^[0-9,]+$/', $line[DRCsvFile::CSV_VALEUR]), "Volume de l'achat");
        continue;
    }

    $t->is($line[DRCsvFile::CSV_TYPE], "DR", "Le type de la ligne est DR");
    $t->is($line[DRCsvFile::CSV_CAMPAGNE], date('Y'), "La campagne est 2017");
    $t->is($line[DRCsvFile::CSV_RECOLTANT_CVI], "7523700100", "Le CVI est 7523700100");
    $t->is($line[DRCsvFile::CSV_RECOLTANT_LIBELLE], "\"ACTUALYS JEAN\"", "Le nom est ACTUALYS JEAN");
    $t->is($line[DRCsvFile::CSV_RECOLTANT_COMMUNE], "NEUILLY", "Le commune est NEUILLY");
    $t->is($line[DRCsvFile::CSV_PRODUIT_CERTIFICATION], $certifications[$application], "certification trouvée pour ".$line[DRCsvFile::CSV_PRODUIT_LIBELLE]);
    $t->is($line[DRCsvFile::CSV_COLONNE_ID], $colonneid, "Bon numéro de colonne : (". $colonneid .") #".$line[DRCsvFile::CSV_COLONNE_ID]);
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

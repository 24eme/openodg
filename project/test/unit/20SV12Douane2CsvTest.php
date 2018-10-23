<?php require_once(dirname(__FILE__).'/../bootstrap/common.php');


$csv = new SV12DouaneCsvFile(dirname(__FILE__).'/../data/sv12_douane_'.$application.'.csv');

$csvConvert = $csv->convert();

$lines = explode("\n", $csvConvert);

$t = new lime_test((count($lines) - 1)*11);
if($application == "rhone") {
  $linesAObtenir = array(
    array('produit' => "1B525", 'produit_libelle' => 'CONDRIEU', 'values' => array(2700, 0.4579, 19.42, 19.42)),
    array('produit' => "1B541", 'produit_libelle' => 'Hermitage ou Ermitage bl', 'values' => array(850,0.1484,6.75,6.75)),
    array('produit' => "1R542", 'produit_libelle' => 'Crozes-Hermitage rg', 'values' => array(7000, 1.08, 54, 54)),
    array('produit' => "1R526", 'produit_libelle' => 'CORNAS', 'values' => array(3000, 0.5495, 23, 23)),
    array('produit' => "1B542", 'produit_libelle' => 'Crozes-Hermitage bl', 'values' => array(5000, 0.8866, 30.96, 30.96)),
    array('produit' => "1R542", 'produit_libelle' => 'Crozes-Hermitage rg', 'values' => array(5528, 0.72, 37.14, 37.14)),
    array('produit' => "1B542", 'produit_libelle' => 'Crozes-Hermitage bl', 'values' => array(800, 0.255, 5.04, 5.04)),
    array('produit' => "1R542", 'produit_libelle' => 'Crozes-Hermitage rg', 'values' => array(15454, 2.2352, 112.86, 112.86)),
    array('produit' => "1R542", 'produit_libelle' => 'Crozes-Hermitage rg', 'values' => array(2000, 0.3234, 16, 16)),
    array('produit' => "1R542", 'produit_libelle' => 'Crozes-Hermitage rg', 'values' => array(2082, 0.312, 15, 15)),
  );
}

if($application == "provence") {
  $linesAObtenir = array(
    array('produit' => "1S582S", 'produit_libelle' => 'Côtes de Provence rosé', 'values' => array(25105, 6.202, 180, 180))
  );
}


$typesLigne = array(
    array("libelle" => "Quantité de VF", "numero" => "07"),
    array("libelle" => "Superficie de récolte", "numero" => "09"),
    array("libelle" => "Volume issu de VF", "numero" => "10"),
    array("libelle" => "Total produit", "numero" => "12"),
);

$i = 0;
foreach($lines as $line) {
    if(!$line) {
        continue;
    }

    $line = explode(";", $line);
    $t->is($line[SV12CsvFile::CSV_TYPE], "SV12", "Le type de la ligne est SV12");
    $t->is($line[SV12CsvFile::CSV_CAMPAGNE], date('Y'), "La campagne est 2017");
    $t->is($line[SV12CsvFile::CSV_RECOLTANT_CVI], "7523700100", "Le CVI est 7523700100");
    $t->is($line[SV12CsvFile::CSV_RECOLTANT_LIBELLE], "\"ACTUALYS JEAN\"", "Le nom est ACTUALYS JEAN");
    $t->is($line[SV12CsvFile::CSV_RECOLTANT_COMMUNE], "NEUILLY", "Le commune est NEUILLY");

    $ligneAObtenir = $linesAObtenir[floor($i/4)];
    $rest = fmod($i, 4);
    $valueAObtenir = $ligneAObtenir['values'][$rest];

    $t->is($line[SV12CsvFile::CSV_PRODUIT_CERTIFICATION], "AOP", "certification trouvée pour ".$ligneAObtenir['produit']);
    $t->is($line[SV12CsvFile::CSV_PRODUIT_INAO], $ligneAObtenir['produit'], "Le code produit est ".$ligneAObtenir['produit']);
    $t->is($line[SV12CsvFile::CSV_PRODUIT_LIBELLE], $ligneAObtenir['produit_libelle'], "Le code produit est ".$ligneAObtenir['produit_libelle']);
    $t->is($line[SV12CsvFile::CSV_LIGNE_CODE], $typesLigne[$rest]['numero'], "Le numéro du type de la ligne est ".$typesLigne[$rest]['numero']);
    $t->is($line[SV12CsvFile::CSV_LIGNE_LIBELLE], $typesLigne[$rest]['libelle'], "La ligne est de type ".$typesLigne[$rest]['libelle']);
    $t->is(round(str_replace(",", ".", $line[SV12CsvFile::CSV_VALEUR]), 4), round($valueAObtenir, 4), "La valeur est ".$valueAObtenir);

    $i++;
}

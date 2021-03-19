<?php require_once(dirname(__FILE__).'/../bootstrap/common.php');

$config = ConfigurationClient::getCurrent();
foreach($config->getProduits() as $produit) {
    if(!$produit->getRendement()) {
        continue;
    }
    break;
}

$csvContentTemplate = file_get_contents(dirname(__FILE__).'/../data/sv11_douane.csv');

$csvTmpFile = tempnam(sys_get_temp_dir(), 'openodg');
file_put_contents($csvTmpFile, str_replace(array("%cvi%", "%code_inao_1%", "%libelle_produit_1%"), array("7523700100", $produit->getCodeDouane(), $produit->getLibelleComplet()), $csvContentTemplate));

$csv = new SV11DouaneCsvFile($csvTmpFile);
$csvConvert = $csv->convert();
unlink($csvTmpFile);

$lines = explode("\n", $csvConvert);


$t = new lime_test(25);
$nb = 0;
foreach($lines as $line) {
    if(!$line) {
        continue;
    }
    $nb++;
}
$t->is($nb, 327, "Le CSV a 4 lignes");

$line = explode(";", $lines[0]);

$t->is($line[SV11CsvFile::CSV_TYPE], "SV11", "Le type de la ligne est SV11");
$year = date('Y');
if (date('m') < 8) {
    $year = $year - 1;
}
$campagne = sprintf("%04d-%04d", $year , $year + 1 );
$t->is($line[SV11CsvFile::CSV_CAMPAGNE], $campagne, "La campagne est ".$campagne);
$t->is($line[SV11CsvFile::CSV_RECOLTANT_CVI], "7523700100", "Le CVI est 7523700100");
$t->is($line[SV11CsvFile::CSV_PRODUIT_CERTIFICATION], $produit->getCertification()->getKey(), "Certification OK");
$t->is($line[SV11CsvFile::CSV_PRODUIT_GENRE], $produit->getGenre()->getKey(), "Genre OK");
$t->is($line[SV11CsvFile::CSV_PRODUIT_APPELLATION], $produit->getAppellation()->getKey(), "Appellation OK");
$t->is($line[SV11CsvFile::CSV_PRODUIT_MENTION], $produit->getMention()->getKey(), "Mention OK");
$t->is($line[SV11CsvFile::CSV_PRODUIT_LIEU], $produit->getLieu()->getKey(), "Lieu OK");
$t->is($line[SV11CsvFile::CSV_PRODUIT_COULEUR], $produit->getCouleur()->getKey(), "Couleur OK");
$t->is($line[SV11CsvFile::CSV_PRODUIT_CEPAGE], $produit->getCepage()->getKey(), "Cepage OK");
$t->is($line[SV11CsvFile::CSV_PRODUIT_INAO], $produit->getCodeDouane().'  ', "Le code inao est OK");
$t->is($line[SV11CsvFile::CSV_PRODUIT_LIBELLE], $produit->getLibelleComplet(), "Libelle complet OK");

$t->is($line[SV11CsvFile::CSV_LIGNE_CODE], "04", "Code du type de mouvement");
$t->is($line[SV11CsvFile::CSV_LIGNE_LIBELLE], "Superficie de récolte", "Libelle du type de mouvement");
$t->is(round(str_replace(",", ".", $line[SV11CsvFile::CSV_VALEUR]), 4), 1.4885, "Valeur");

$line = explode(";", $lines[1]);
$t->is($line[SV11CsvFile::CSV_LIGNE_CODE], "05", "Code du type de mouvement");
$t->is($line[SV11CsvFile::CSV_LIGNE_LIBELLE], "Récolte", "Libelle du type de mouvement");
$t->is(round(str_replace(",", ".", $line[SV11CsvFile::CSV_VALEUR]), 4), 56.29, "Valeur");

$line = explode(";", $lines[2]);
$t->is($line[SV11CsvFile::CSV_LIGNE_CODE], "10", "Code du type de mouvement");
$t->is($line[SV11CsvFile::CSV_LIGNE_LIBELLE], "Volume produit apte à", "Libelle du type de mouvement");
$t->is(round(str_replace(",", ".", $line[SV11CsvFile::CSV_VALEUR]), 4), 53.59, "Valeur");

$line = explode(";", $lines[3]);
$t->is($line[SV11CsvFile::CSV_LIGNE_CODE], "11", "Code du type de mouvement");
$t->is($line[SV11CsvFile::CSV_LIGNE_LIBELLE], "Vol à livrer à distillation ou usages indistriels", "Libelle du type de mouvement");
$t->is(round(str_replace(",", ".", $line[SV11CsvFile::CSV_VALEUR]), 4), 2.70, "Valeur");

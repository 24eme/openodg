<?php require_once(dirname(__FILE__).'/../bootstrap/common.php');

$config = ConfigurationClient::getCurrent();
$produit1 = null;
$produit2 = null;
foreach($config->getProduits() as $produit) {
    if(!$produit->getRendement()) {
        continue;
    }
    if(!$produit1) {
        $produit1 = $produit;
        continue;
    } elseif(!$produit2) {
        $produit2 = $produit;
        continue;
    }

    break;
}

$csvContentTemplate = file_get_contents(dirname(__FILE__).'/../data/dr_douane.csv');

$csvTmpFile = tempnam(sys_get_temp_dir(), 'openodg');
file_put_contents($csvTmpFile, str_replace(array("%code_inao_1%", "%libelle_produit_1%","%code_inao_2%", "%libelle_produit_2%"), array($produit1->getCodeDouane(), $produit1->getLibelleComplet(), $produit2->getCodeDouane(), $produit2->getLibelleComplet()), $csvContentTemplate));

$csv = new DRDouaneCsvFile($csvTmpFile);
$csvConvert = $csv->convert();
unlink($csvTmpFile);

$lines = explode("\n", $csvConvert);

$t = new lime_test(15);
$nb = 0;
foreach($lines as $line) {
    if(!$line) {
        continue;
    }
    $nb++;
}
$t->is($nb, 59, "Le CSV a 4 lignes");

$line = explode(";", $lines[0]);

$t->is($line[DRCsvFile::CSV_TYPE], "DR", "Le type de la ligne est DR");
$t->is($line[DRCsvFile::CSV_CAMPAGNE], date('Y'), "La campagne est ".date('Y'));
$t->is($line[DRCsvFile::CSV_RECOLTANT_CVI], "7523700100", "Le CVI est 7523700100");
$t->is($line[DRCsvFile::CSV_RECOLTANT_LIBELLE], "\"ACTUALYS JEAN\"", "Le nom est ACTUALYS JEAN");
$t->is($line[DRCsvFile::CSV_RECOLTANT_COMMUNE], "NEUILLY", "Le commune est NEUILLY");
$t->is($line[DRCsvFile::CSV_PRODUIT_CERTIFICATION], $produit1->getCertification()->getKey(), "Certification OK");
$t->is($line[DRCsvFile::CSV_PRODUIT_GENRE], $produit1->getGenre()->getKey(), "Genre OK");
$t->is($line[DRCsvFile::CSV_PRODUIT_APPELLATION], $produit1->getAppellation()->getKey(), "Appellation OK");
$t->is($line[DRCsvFile::CSV_PRODUIT_MENTION], $produit1->getMention()->getKey(), "Mention OK");
$t->is($line[DRCsvFile::CSV_PRODUIT_LIEU], $produit1->getLieu()->getKey(), "Lieu OK");
$t->is($line[DRCsvFile::CSV_PRODUIT_COULEUR], $produit1->getCouleur()->getKey(), "Couleur OK");
$t->is($line[DRCsvFile::CSV_PRODUIT_CEPAGE], $produit1->getCepage()->getKey(), "Cepage OK");
$t->is($line[DRCsvFile::CSV_PRODUIT_INAO], $produit1->getCodeDouane(), "Le code inao est OK");
$t->is($line[DRCsvFile::CSV_PRODUIT_LIBELLE], $produit1->getLibelleComplet(), "Libelle complet OK");

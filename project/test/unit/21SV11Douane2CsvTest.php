<?php require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(38);

$config = ConfigurationClient::getCurrent();
foreach($config->getProduits() as $produit) {
    if(!$produit->getRendement()) {
        continue;
    }
    break;
}

$csvContentTemplate = file_get_contents(dirname(__FILE__).'/../data/sv11_douane.csv');

$csvTmpFile = tempnam(sys_get_temp_dir(), 'openodg');
file_put_contents($csvTmpFile, str_replace(array("%cvi%", "%cvi_1%", "%code_inao_1%", "%libelle_produit_1%"), array("7523700100", "7523700800", $produit->getCodeDouane(), $produit->getLibelleComplet()), $csvContentTemplate));

$csv = new SV11DouaneCsvFile($csvTmpFile);
$t->is($csv->detectFormat(), "XlsSV11", "Détéction du format du fichier XlsSV11");

$csvConvert = $csv->convert();
unlink($csvTmpFile);

$linesXlsSV11 = explode("\n", $csvConvert);

$nb = 0;
foreach($linesXlsSV11 as $line) {
    if(!$line) {
        continue;
    }
    $nb++;
}
$t->is($nb, 327, "Le CSV a 327 lignes");

$line = explode(";", $linesXlsSV11[0]);

$t->is($line[SV11CsvFile::CSV_TYPE], "SV11", "Le type de la ligne est SV11");
$year = date('Y');
if (date('m') < 8) {
    $year = $year - 1;
}
$campagne = sprintf("%04d-%04d", $year , $year + 1 );
$t->is($line[SV11CsvFile::CSV_CAMPAGNE], $campagne, "La campagne est ".$campagne);
$t->is($line[SV11CsvFile::CSV_RECOLTANT_CVI], '"7523700100"', "Le CVI est 7523700100 en chaine de caractère");
$t->is($line[SV11CsvFile::CSV_PRODUIT_CERTIFICATION], $produit->getCertification()->getKey(), "Certification OK");
$t->is($line[SV11CsvFile::CSV_PRODUIT_GENRE], $produit->getGenre()->getKey(), "Genre OK");
$t->is($line[SV11CsvFile::CSV_PRODUIT_APPELLATION], $produit->getAppellation()->getKey(), "Appellation OK");
$t->is($line[SV11CsvFile::CSV_PRODUIT_MENTION], $produit->getMention()->getKey(), "Mention OK");
$t->is($line[SV11CsvFile::CSV_PRODUIT_LIEU], $produit->getLieu()->getKey(), "Lieu OK");
$t->is($line[SV11CsvFile::CSV_PRODUIT_COULEUR], $produit->getCouleur()->getKey(), "Couleur OK");
$t->is($line[SV11CsvFile::CSV_PRODUIT_CEPAGE], $produit->getCepage()->getKey(), "Cepage OK");
$t->is($line[SV11CsvFile::CSV_PRODUIT_INAO], $produit->getCodeDouane().'  ', "Le code inao est OK");
$t->is($line[SV11CsvFile::CSV_PRODUIT_LIBELLE], $produit->getLibelleComplet(), "Libelle complet OK");
$t->is($line[SV11CsvFile::CSV_PRODUIT_COMPLEMENT], null, "Colonne dénomination complémentaire vide");

$t->is($line[SV11CsvFile::CSV_LIGNE_CODE], SV11CsvFile::CSV_LIGNE_CODE_SUPERFICIE, "Code du type de mouvement");
$t->is($line[SV11CsvFile::CSV_LIGNE_LIBELLE], "4. Superficie de récolte calculée (ratio bailleur/metayer) - Superficie de récolte", "Libelle du type de mouvement");
$t->is(round(str_replace(",", ".", $line[SV11CsvFile::CSV_VALEUR]), 4), 1.4885, "Valeur");

$line = explode(";", $linesXlsSV11[1]);
$t->is($line[SV11CsvFile::CSV_LIGNE_CODE], SV11CsvFile::CSV_LIGNE_CODE_LIVRAISON_COOP, "Code du type de mouvement");
$t->is($line[SV11CsvFile::CSV_LIGNE_LIBELLE], "8. Récolte à une cave coopérative par l'adhérent - Récolte", "Libelle du type de mouvement");
$t->is(round(str_replace(",", ".", $line[SV11CsvFile::CSV_VALEUR]), 4), 56.29, "Valeur");

$line = explode(";", $linesXlsSV11[2]);
$t->is($line[SV11CsvFile::CSV_LIGNE_CODE], SV11CsvFile::CSV_LIGNE_CODE_VOLUME_APTE, "Code du type de mouvement");
$t->is($line[SV11CsvFile::CSV_LIGNE_LIBELLE], "15. Vol. de vin avec AO/IGP avec/sans cépage dans la limite du rdt autorisé - Volume produit apte à", "Libelle du type de mouvement");
$t->is(round(str_replace(",", ".", $line[SV11CsvFile::CSV_VALEUR]), 4), 53.59, "Valeur");

$line = explode(";", $linesXlsSV11[3]);
$t->is($line[SV11CsvFile::CSV_LIGNE_CODE], SV11CsvFile::CSV_LIGNE_CODE_VOLUME_ELIMINIE, "Code du type de mouvement");
$t->is($line[SV11CsvFile::CSV_LIGNE_LIBELLE], "16. Vol. vin dépassement du rdt autorisé en AOP à livrer aux usages industriels - Vol à livrer à distillation ou usages indistriels", "Libelle du type de mouvement");
$t->is(round(str_replace(",", ".", $line[SV11CsvFile::CSV_VALEUR]), 4), 2.70, "Valeur");

$t->is($line[SV11CsvFile::CSV_COLONNE_ID], '1', "Colonne colonne id OK");
$t->is($line[SV11CsvFile::CSV_ORGANISME], strtoupper($application), "Colonne organisme id OK");
$t->is($line[SV11CsvFile::CSV_MILLESIME], $year, "Colonne Millesime $year OK");
$t->is($line[SV11CsvFile::CSV_FAMILLE_LIGNE_CALCULEE], "COOPERATIVE", "Colonne famille calculée OK");

$line = explode(";", $linesXlsSV11[4]);
$t->is($line[SV11CsvFile::CSV_PRODUIT_COMPLEMENT], "BIO", "Colonne dénomination complémentaire de la 2ème ligne");

$csvContentTemplate = file_get_contents(dirname(__FILE__).'/../data/sv11_douane_vendanges.csv');

$csvTmpFile = tempnam(sys_get_temp_dir(), 'openodg').'-7523700100_'.date('Y').'00000000000C_'.date('dmYhis');
file_put_contents($csvTmpFile, str_replace(array("%cvi_1%", "%code_inao_1%", "%libelle_produit_1%"), array("7523700800", $produit->getCodeDouane(), $produit->getLibelleComplet()), $csvContentTemplate));

$csv = new SV11DouaneCsvFile($csvTmpFile);
$t->is($csv->detectFormat(), "CsvVendanges", "Détéction du format du fichier CsvVendanges");
$csvConvert = $csv->convert();
unlink($csvTmpFile);

$linesCsvVendanges = explode("\n", $csvConvert);

$replacePattern = "/(ACTUALYS JEAN|NEUILLY|AUBIGNAN| - [^;]*)/";

$t->is(preg_replace($replacePattern, "", $linesCsvVendanges[0]), preg_replace($replacePattern, "", $linesXlsSV11[0]), "Les 2 conversions donnent le même résultat sur la 1ère ligne");
$t->is(preg_replace($replacePattern, "", $linesCsvVendanges[1]), preg_replace($replacePattern, "", $linesXlsSV11[1]), "Les 2 conversions donnent le même résultat sur la 2ème ligne");
$t->is(preg_replace($replacePattern, "", $linesCsvVendanges[2]), preg_replace($replacePattern, "", $linesXlsSV11[2]), "Les 2 conversions donnent le même résultat sur la 3ème ligne");
$t->is(preg_replace($replacePattern, "", $linesCsvVendanges[3]), preg_replace($replacePattern, "", $linesXlsSV11[3]), "Les 2 conversions donnent le même résultat sur la 4ème ligne");
$t->is(preg_replace($replacePattern, "", $linesCsvVendanges[4]), preg_replace($replacePattern, "", $linesXlsSV11[(4)]), "Les 2 conversions donnent le même résultat sur la 5ème ligne");

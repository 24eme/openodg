<?php require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(42);

$config = ConfigurationClient::getCurrent();
foreach($config->getProduits() as $produit) {
    if(!$produit->getRendement()) {
        continue;
    }
    break;
}

$t->ok($produit->getLibelleComplet(), "configuration de base est OK : on a un libellé de produit");
$t->ok($produit->getCodeDouane(), "configuration de base est OK : le produit a un code douane");

$csvContentTemplate = file_get_contents(dirname(__FILE__).'/../data/sv12_douane.csv');

$csvTmpFile = tempnam(sys_get_temp_dir(), 'openodg');
file_put_contents($csvTmpFile, str_replace(array("%cvi%", "%cvi_1%", "%code_inao%", "%libelle_produit%"), array("7523700100", "7523700800", $produit->getCodeDouane(), $produit->getLibelleComplet()), $csvContentTemplate));

$csv = new SV12DouaneCsvFile($csvTmpFile);
$t->is($csv->detectFormat(), "XlsSV12", "Détéction du format du fichier XlsSV12");
$csvConvert = $csv->convert();
unlink($csvTmpFile);

$linesXlsSV12 = explode("\n", $csvConvert);


$nb = 0;
foreach($linesXlsSV12 as $line) {
    if(!$line) {
        continue;
    }
    $nb++;
}
$t->is($nb, 5, "Le CSV a 5 lignes");

$line = explode(";", $linesXlsSV12[0]);


$t->is($line[SV12CsvFile::CSV_TYPE], "SV12", "Le type de la ligne est SV12");
$year = date('Y');
if (date('m') < 8) {
    $year = $year - 1;
}
$campagne = sprintf("%04d-%04d", $year , $year + 1 );
$t->is($line[SV12CsvFile::CSV_CAMPAGNE], $campagne, "La campagne est $campagne");
$t->is($line[SV12CsvFile::CSV_RECOLTANT_CVI], '"7523700100"', "Le CVI est 7523700100 en chaine de caractères");
$t->is($line[SV12CsvFile::CSV_PRODUIT_CERTIFICATION], $produit->getCertification()->getKey(), "Certification OK");
$t->is($line[SV12CsvFile::CSV_PRODUIT_GENRE], $produit->getGenre()->getKey(), "Genre OK");
$t->is($line[SV12CsvFile::CSV_PRODUIT_APPELLATION], $produit->getAppellation()->getKey(), "Appellation OK");
$t->is($line[SV12CsvFile::CSV_PRODUIT_MENTION], $produit->getMention()->getKey(), "Mention OK");
$t->is($line[SV12CsvFile::CSV_PRODUIT_LIEU], $produit->getLieu()->getKey(), "Lieu OK");
$t->is($line[SV12CsvFile::CSV_PRODUIT_COULEUR], $produit->getCouleur()->getKey(), "Couleur OK");
$t->is($line[SV12CsvFile::CSV_PRODUIT_CEPAGE], $produit->getCepage()->getKey(), "Cepage OK");
$t->is($line[SV12CsvFile::CSV_PRODUIT_INAO], $produit->getCodeDouane(), "Le code inao est OK");
$t->is($line[SV12CsvFile::CSV_PRODUIT_LIBELLE], $produit->getLibelleComplet(), "Libelle complet OK");

$t->is($line[SV12CsvFile::CSV_LIGNE_CODE], SV12CsvFile::CSV_LIGNE_CODE_RECOLTE_RAISINS, "Code du type de mouvement");
$t->is($line[SV12CsvFile::CSV_LIGNE_LIBELLE], "6. Récolte sous forme de raisins en kg - Quantité de VF", "Libelle du type de mouvement");
$t->is(round(str_replace(",", ".", $line[SV12CsvFile::CSV_VALEUR]), 4), 25105, "Valeur");

$line = explode(";", $linesXlsSV12[1]);
$t->is($line[SV12CsvFile::CSV_LIGNE_CODE], SV12CsvFile::CSV_LIGNE_CODE_SUPERFICIE, "Code du type de mouvement");
$t->is($line[SV12CsvFile::CSV_LIGNE_LIBELLE], "4. Superficie de récolte calculée (ratio bailleur/metayer) - Superficie de récolte", "Libelle du type de mouvement");
$t->is(round(str_replace(",", ".", $line[SV12CsvFile::CSV_VALEUR]), 4), 6.202, "Valeur");

$line = explode(";", $linesXlsSV12[2]);
$t->is($line[SV12CsvFile::CSV_LIGNE_CODE], SV12CsvFile::CSV_LIGNE_CODE_VOLUME_RAISINS, "Code du type de mouvement");
$t->is($line[SV12CsvFile::CSV_LIGNE_LIBELLE], "15. Vol. de vin clair issu de VF - Volume issu de VF", "Libelle du type de mouvement");
$t->is(round(str_replace(",", ".", $line[SV12CsvFile::CSV_VALEUR]), 4), 170, "Valeur");

$line = explode(";", $linesXlsSV12[3]);
$t->is($line[SV12CsvFile::CSV_LIGNE_CODE], SV12CsvFile::CSV_LIGNE_CODE_VOLUME_MOUTS, "Code du type de mouvement");
$t->is($line[SV12CsvFile::CSV_LIGNE_LIBELLE], "15. Vol. de vin clair issu de mouts - Volume issu de moûts", "Libelle du type de mouvement");
$t->is(round(str_replace(",", ".", $line[SV12CsvFile::CSV_VALEUR]), 4), 10, "Valeur");

$line = explode(";", $linesXlsSV12[4]);
$t->is($line[SV12CsvFile::CSV_LIGNE_CODE], SV12CsvFile::CSV_LIGNE_CODE_VOLUME_TOTAL, "Code du type de mouvement");
$t->is($line[SV12CsvFile::CSV_LIGNE_LIBELLE], "15. Vol. de vin avec AO/IGP avec/sans cépage dans la limite du rdt autorisé - Total produit", "Libelle du type de mouvement");
$t->is(round(str_replace(",", ".", $line[SV12CsvFile::CSV_VALEUR]), 4), 180, "Valeur");

$t->is($line[SV12CsvFile::CSV_COLONNE_ID], '1', "Colonne colonne id OK");
$t->is($line[SV12CsvFile::CSV_ORGANISME], strtoupper($application), "Colonne organisme id OK");
$t->is($line[SV12CsvFile::CSV_MILLESIME], $year, "Colonne Millesime $year OK");
$t->is($line[SV12CsvFile::CSV_FAMILLE_LIGNE_CALCULEE], "NEGOCIANT_VINIFICATEUR", "Colonne famille calculée OK");

$csvContentTemplate = file_get_contents(dirname(__FILE__).'/../data/sv12_douane_vendanges.csv');

$csvTmpFile = tempnam(sys_get_temp_dir(), 'openodg').'-7523700100_'.date('Y').'00000000000C_'.date('dmYhis');
file_put_contents($csvTmpFile, str_replace(array("%cvi%", "%cvi_1%", "%code_inao%", "%libelle_produit%"), array("7523700100", "7523700800", $produit->getCodeDouane(), $produit->getLibelleComplet()), $csvContentTemplate));

$csv = new SV12DouaneCsvFile($csvTmpFile);
$t->is($csv->detectFormat(), "CsvVendanges", "Détéction du format du fichier CsvVendanges");
$csvConvert = $csv->convert();
unlink($csvTmpFile);

$linesCsvVendanges = explode("\n", $csvConvert);

$replacePattern = "/(ACTUALYS JEAN|NEUILLY|GONFARON| - [^;]*)/";

$t->is(preg_replace($replacePattern, "", $linesCsvVendanges[0]), preg_replace($replacePattern, "", $linesXlsSV12[0]), "Les 2 conversions donnent le même résultat sur la 1ère ligne");
$t->is(preg_replace($replacePattern, "", $linesCsvVendanges[1]), preg_replace($replacePattern, "", $linesXlsSV12[1]), "Les 2 conversions donnent le même résultat sur la 2ème ligne");
$t->is(preg_replace($replacePattern, "", $linesCsvVendanges[2]), preg_replace($replacePattern, "", $linesXlsSV12[2]), "Les 2 conversions donnent le même résultat sur la 3ème ligne");
$t->is(preg_replace($replacePattern, "", $linesCsvVendanges[3]), preg_replace($replacePattern, "", $linesXlsSV12[3]), "Les 2 conversions donnent le même résultat sur la 4ème ligne");
$t->is(preg_replace($replacePattern, "", $linesCsvVendanges[4]), preg_replace($replacePattern, "", $linesXlsSV12[4]), "Les 2 conversions donnent le même résultat sur la 5ème ligne");
$t->is(preg_replace($replacePattern, "", $linesCsvVendanges[5]), preg_replace($replacePattern, "", $linesXlsSV12[5]), "Les 2 conversions donnent le même résultat sur la 6ème ligne");

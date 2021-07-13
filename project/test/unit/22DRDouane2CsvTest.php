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
file_put_contents($csvTmpFile, str_replace(array("%cvi%", "%code_inao_1%", "%libelle_produit_1%","%code_inao_2%", "%libelle_produit_2%"), array("7523700100", $produit1->getCodeDouane(), $produit1->getLibelleComplet(), $produit2->getCodeDouane(), $produit2->getLibelleComplet()), $csvContentTemplate));

$csv = new DRDouaneCsvFile($csvTmpFile);
$csvConvert = $csv->convert();
unlink($csvTmpFile);

$lines = explode("\n", $csvConvert);

$t = new lime_test(74);
$nb = 0;
foreach($lines as $line) {
    if(!preg_match('/[0-9]/', $line)) {
        continue;
    }
    $nb++;
}
$t->is($nb, 88, "Le CSV a 88 lignes");
$line = explode(";", $lines[count($lines) - 2]);
$t->is($line[DRCsvFile::CSV_COLONNE_ID], '8', 'On a bien repéré 8 colonnes');

$line = explode(";", $lines[0]);

$t->is($line[DRCsvFile::CSV_TYPE], "DR", "Le type de la ligne est DR");
$year = date('Y');
if (date('m') < 8) {
    $year = $year - 1;
}
$campagne = sprintf("%04d-%04d", $year , $year + 1 );
$t->is($line[SV11CsvFile::CSV_CAMPAGNE], $campagne, "La campagne est ".$campagne);
$t->is($line[DRCsvFile::CSV_RECOLTANT_CVI], "7523700100", "Le CVI est 7523700100");
$t->is($line[DRCsvFile::CSV_PRODUIT_CERTIFICATION], $produit1->getCertification()->getKey(), "Certification OK");
$t->is($line[DRCsvFile::CSV_PRODUIT_GENRE], $produit1->getGenre()->getKey(), "Genre OK");
$t->is($line[DRCsvFile::CSV_PRODUIT_APPELLATION], $produit1->getAppellation()->getKey(), "Appellation OK");
$t->is($line[DRCsvFile::CSV_PRODUIT_MENTION], $produit1->getMention()->getKey(), "Mention OK");
$t->is($line[DRCsvFile::CSV_PRODUIT_LIEU], $produit1->getLieu()->getKey(), "Lieu OK");
$t->is($line[DRCsvFile::CSV_PRODUIT_COULEUR], $produit1->getCouleur()->getKey(), "Couleur OK");
$t->is($line[DRCsvFile::CSV_PRODUIT_CEPAGE], $produit1->getCepage()->getKey(), "Cepage OK");
$t->is($line[DRCsvFile::CSV_PRODUIT_INAO], $produit1->getCodeDouane(), "Le code inao est OK");
$t->is($line[DRCsvFile::CSV_PRODUIT_LIBELLE], $produit1->getLibelleComplet(), "Libelle complet OK");

$line = explode(";", $lines[63]);
$t->is($line[DRCsvFile::CSV_COLONNE_ID], '7', "le numéro de colonne est ok pour la ligne superficie producteur (colonne 7)");
$t->is($line[DRCsvFile::CSV_LIGNE_CODE], '4', "le code de la ligne superficie producteur est OK (colonne 7)");
$t->is($line[DRCsvFile::CSV_LIGNE_LIBELLE], 'Superficie de récolte', "le libellé de la ligne superficie producteur est OK (colonne 7)");
$t->is($line[DRCsvFile::CSV_BAILLEUR_NOM], '', "la ligne superficie producteur n'a pas de nom de bailleur (colonne 7)");
$t->is($line[DRCsvFile::CSV_BAILLEUR_PPM], '', "la ligne superficie producteur n'a pas de ppm de bailleur (colonne 7)");
$t->is($line[DRCsvFile::CSV_VALEUR], '0,0760', "la superficie est bonne pour le producteur (colonne 7)");
$superficie_producteur = floatval(preg_replace('/,/', '.', $line[DRCsvFile::CSV_VALEUR]));
$line = explode(";", $lines[64]);
$t->is($line[DRCsvFile::CSV_COLONNE_ID], '7', "le numéro de colonne est ok pour la ligne superficie bailleur (colonne 7)");
$t->is($line[DRCsvFile::CSV_LIGNE_CODE], '4', "le code de la ligne superficie bailleur est OK (colonne 7)");
$t->is($line[DRCsvFile::CSV_LIGNE_LIBELLE], 'Superficie de récolte', "le libellé de la ligne superficie bailleur est OK (colonne 7)");
$t->is($line[DRCsvFile::CSV_BAILLEUR_NOM], 'XXXXXX Simon', "la ligne superficie bailleur a un nom de bailleur (colonne 7)");
$t->is($line[DRCsvFile::CSV_BAILLEUR_PPM], 'Z00000999', "la ligne superficie bailleur a un ppm de bailleur (colonne 7)");
$t->is($line[DRCsvFile::CSV_VALEUR], '0,0114', "la superficie est bonne pour le bailleur (colonne 7)");
$superficie_bailleur = floatval(preg_replace('/,/', '.', $line[DRCsvFile::CSV_VALEUR]));
$line = explode(";", $lines[65]);
$t->is($line[DRCsvFile::CSV_COLONNE_ID], '7', "le numéro de colonne est ok pour la ligne superficie originale (colonne 7)");
$t->is($line[DRCsvFile::CSV_LIGNE_CODE], '04b', "le code de la ligne superficie originale est OK (colonne 7)");
$t->is($line[DRCsvFile::CSV_LIGNE_LIBELLE], 'Superificie de récolte originale', "le libellé de la ligne superficie originale est OK (colonne 7)");
$t->is($line[DRCsvFile::CSV_BAILLEUR_NOM], '', "la ligne superficie originale n'a pas de nom de bailleur (colonne 7)");
$t->is($line[DRCsvFile::CSV_BAILLEUR_PPM], '', "la ligne superficie originale n'a pas de ppm de bailleur (colonne 7)");
$t->is($line[DRCsvFile::CSV_VALEUR], '0,0874', "la superficie originale est bonne (colonne 7)");
$superficie_totale = floatval(preg_replace('/,/', '.', $line[DRCsvFile::CSV_VALEUR]));
$line = explode(";", $lines[66]);
$t->is($line[DRCsvFile::CSV_COLONNE_ID], '7', "le numéro de colonne est ok pour la ligne récolte producteur (colonne 7)");
$t->is($line[DRCsvFile::CSV_LIGNE_CODE], '5', "le code de la ligne récolte producteur est OK (colonne 7)");
$t->is($line[DRCsvFile::CSV_LIGNE_LIBELLE], 'Récolte totale', "le libellé de la ligne récolte producteur est OK (colonne 7)");
$t->is($line[DRCsvFile::CSV_BAILLEUR_NOM], '', "la ligne récolte producteur n'a pas de nom de bailleur (colonne 7)");
$t->is($line[DRCsvFile::CSV_BAILLEUR_PPM], '', "la ligne récolte producteur n'a pas de ppm de bailleur (colonne 7)");
$t->is($line[DRCsvFile::CSV_VALEUR], '6,21', "la récolte est bonne pour le producteur (colonne 7)");
$recolte_producteur = floatval(preg_replace('/,/', '.', $line[DRCsvFile::CSV_VALEUR]));
$line = explode(";", $lines[67]);
$t->is($line[DRCsvFile::CSV_COLONNE_ID], '7', "le numéro de colonne est ok pour la ligne récolte bailleur (colonne 7)");
$t->is($line[DRCsvFile::CSV_LIGNE_CODE], '5', "le code de la ligne récolte bailleur est OK (colonne 7)");
$t->is($line[DRCsvFile::CSV_LIGNE_LIBELLE], 'Récolte totale', "le libellé de la ligne récolte bailleur est OK (colonne 7)");
$t->is($line[DRCsvFile::CSV_BAILLEUR_NOM], 'XXXXXX Simon', "la ligne récolte bailleur a un nom de bailleur (colonne 7)");
$t->is($line[DRCsvFile::CSV_BAILLEUR_PPM], 'Z00000999', "la ligne récolte bailleur a un ppm de bailleur (colonne 7)");
$t->is($line[DRCsvFile::CSV_VALEUR], '0,93', "la récolte est bonne pour le bailleur (colonne 7)");
$recolte_bailleur = floatval(preg_replace('/,/', '.', $line[DRCsvFile::CSV_VALEUR]));
//$t->is($superficie_producteur / $superficie_totale, $recolte_producteur / ($recolte_bailleur + $recolte_producteur), 'le ratio bailleur / producteur est le meme pour la superficie que pour la récolte (colonne 7)');

$line = explode(";", $lines[76]);
$t->is($line[DRCsvFile::CSV_COLONNE_ID], '8', "le numéro de colonne est ok pour la ligne superficie producteur (dernière colonne)");
$t->is($line[DRCsvFile::CSV_LIGNE_CODE], '4', "le code de la ligne superficie producteur est OK (dernière colonne)");
$t->is($line[DRCsvFile::CSV_LIGNE_LIBELLE], 'Superficie de récolte', "le libellé de la ligne superficie producteur est OK (dernière colonne)");
$t->is($line[DRCsvFile::CSV_BAILLEUR_NOM], '', "la ligne superficie producteur n'a pas de nom de bailleur (dernière colonne)");
$t->is($line[DRCsvFile::CSV_BAILLEUR_PPM], '', "la ligne superficie producteur n'a pas de ppm de bailleur (dernière colonne)");
$t->is($line[DRCsvFile::CSV_VALEUR], '0,0003', "la superficie est bonne pour le producteur (dernière colonne)");
$superficie_producteur = floatval(preg_replace('/,/', '.', $line[DRCsvFile::CSV_VALEUR]));
$line = explode(";", $lines[77]);
$t->is($line[DRCsvFile::CSV_COLONNE_ID], '8', "le numéro de colonne est ok pour la ligne superficie bailleur (dernière colonne)");
$t->is($line[DRCsvFile::CSV_LIGNE_CODE], '4', "le code de la ligne superficie bailleur est OK (dernière colonne)");
$t->is($line[DRCsvFile::CSV_LIGNE_LIBELLE], 'Superficie de récolte', "le libellé de la ligne superficie bailleur est OK (dernière colonne)");
$t->is($line[DRCsvFile::CSV_BAILLEUR_NOM], 'XXXXXX Simon', "la ligne superficie bailleur a un nom de bailleur (dernière colonne)");
$t->is($line[DRCsvFile::CSV_BAILLEUR_PPM], 'Z00000999', "la ligne superficie bailleur a un ppm de bailleur (dernière colonne)");
$t->is($line[DRCsvFile::CSV_VALEUR], '0,0746', "la superficie est bonne pour le bailleur (dernière colonne)");
$superficie_bailleur = floatval(preg_replace('/,/', '.', $line[DRCsvFile::CSV_VALEUR]));
$line = explode(";", $lines[78]);
$t->is($line[DRCsvFile::CSV_COLONNE_ID], '8', "le numéro de colonne est ok pour la ligne superficie originale (dernière colonne)");
$t->is($line[DRCsvFile::CSV_LIGNE_CODE], '04b', "le code de la ligne superficie originale est OK (dernière colonne)");
$t->is($line[DRCsvFile::CSV_LIGNE_LIBELLE], 'Superificie de récolte originale', "le libellé de la ligne superficie originale est OK (dernière colonne)");
$t->is($line[DRCsvFile::CSV_BAILLEUR_NOM], '', "la ligne superficie originale n'a pas de nom de bailleur (dernière colonne)");
$t->is($line[DRCsvFile::CSV_BAILLEUR_PPM], '', "la ligne superficie originale n'a pas de ppm de bailleur (dernière colonne)");
$t->is($line[DRCsvFile::CSV_VALEUR], '0,0749', "la superficie originale est bonne (dernière colonne)");
$superficie_totale = floatval(preg_replace('/,/', '.', $line[DRCsvFile::CSV_VALEUR]));
$line = explode(";", $lines[79]);
$t->is($line[DRCsvFile::CSV_COLONNE_ID], '8', "le numéro de colonne est ok pour la ligne récolte producteur (dernière colonne)");
$t->is($line[DRCsvFile::CSV_LIGNE_CODE], '5', "le code de la ligne récolte producteur est OK (dernière colonne)");
$t->is($line[DRCsvFile::CSV_LIGNE_LIBELLE], 'Récolte totale', "le libellé de la ligne récolte producteur est OK (dernière colonne)");
$t->is($line[DRCsvFile::CSV_BAILLEUR_NOM], '', "la ligne récolte producteur n'a pas de nom de bailleur (dernière colonne)");
$t->is($line[DRCsvFile::CSV_BAILLEUR_PPM], '', "la ligne récolte producteur n'a pas de ppm de bailleur (dernière colonne)");
$t->is($line[DRCsvFile::CSV_VALEUR], '0,01', "la récolte est bonne pour le producteur (dernière colonne)");
$recolte_producteur = floatval(preg_replace('/,/', '.', $line[DRCsvFile::CSV_VALEUR]));
$line = explode(";", $lines[80]);
$t->is($line[DRCsvFile::CSV_COLONNE_ID], '8', "le numéro de colonne est ok pour la ligne récolte bailleur (dernière colonne)");
$t->is($line[DRCsvFile::CSV_LIGNE_CODE], '5', "le code de la ligne récolte bailleur est OK (dernière colonne)");
$t->is($line[DRCsvFile::CSV_LIGNE_LIBELLE], 'Récolte totale', "le libellé de la ligne récolte bailleur est OK (dernière colonne)");
$t->is($line[DRCsvFile::CSV_BAILLEUR_NOM], 'XXXXXX Simon', "la ligne récolte bailleur a un nom de bailleur (dernière colonne)");
$t->is($line[DRCsvFile::CSV_BAILLEUR_PPM], 'Z00000999', "la ligne récolte bailleur a un ppm de bailleur (dernière colonne)");
$t->is($line[DRCsvFile::CSV_VALEUR], '2,58', "la récolte est bonne pour le bailleur (dernière colonne)");
$recolte_bailleur = floatval(preg_replace('/,/', '.', $line[DRCsvFile::CSV_VALEUR]));
//$t->is($superficie_producteur / $superficie_totale, $recolte_producteur / ($recolte_bailleur + $recolte_producteur), 'le ratio bailleur / producteur est le meme pour la superficie que pour la récolte (dernière colonne)');

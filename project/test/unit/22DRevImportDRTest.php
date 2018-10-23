<?php require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(3);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
//Suppression des DRev précédentes
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $drev = DRevClient::getInstance()->find($k);
    $drev->delete(false);
}

$campagne = (date('Y')-1)."";

$csvDouane = new DRDouaneCsvFile(dirname(__FILE__).'/../data/dr_douane_'.$application.'.csv');
$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $campagne);
$csv = $drev->getCsvFromObjectDouanier($csvDouane);

$drev->importCSVDouane($csv);
$drev->save();

$produits = array();
foreach ($csv as $line) {
  $produits[$line[DouaneCsvFile::CSV_PRODUIT_INAO]] = $line[DouaneCsvFile::CSV_PRODUIT_INAO];
}

$t->comment("test sur ".$drev->_id);
$nb_produits_csv = count(array_keys($produits));
$t->is(count($drev->declaration), $nb_produits_csv, "bon nombre de produit ");
$nb = 0;
foreach ($drev->declaration as $hash => $detail) {
  $nb += count($detail);
}
$nb_produits_csv_doublons = $nb_produits_csv;
if (DRevConfiguration::getInstance()->hasDuplicateBio()) {
    $nb_produits_csv_doublons *= 2;
}
$t->is($nb, $nb_produits_csv_doublons, "bon nombre de produit si l'option automatique bio activée");
$drev->delete();

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $campagne);
$csv[0][18] = "vin bio";
$drev->importCSVDouane($csv);
$drev->save();
$nb = 0;
foreach ($drev->declaration as $hash => $detail) {
  $nb += count($detail);
}
$t->is($nb, $nb_produits_csv, "bon nombre de produit si l'option automatique bio activée et présence d'un bio en complement");

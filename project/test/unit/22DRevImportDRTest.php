<?php require_once(dirname(__FILE__).'/../bootstrap/common.php');

$t = new lime_test(12);
$t->comment("test Import DR avec denomination automatique à ".DRevConfiguration::getInstance()->hasDenominationAuto());

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
//Suppression des DRev précédentes
foreach(DRevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $drev = DRevClient::getInstance()->find($k);
    $drev->delete(false);
}

$t->comment("test avec le viti ".$viti->identifiant." (cvi:".$viti->cvi.")");

$periode = (date('Y')-1)."";

$csvDouane = new DRDouaneCsvFile(dirname(__FILE__).'/../data/dr_douane_'.$application.'.csv');
file_put_contents(dirname(__FILE__).'/../data/dr_douane_'.$application.'_converti.csv', $csvDouane->convert());
$drCsv = new DRCsvFile(dirname(__FILE__).'/../data/dr_douane_'.$application.'_converti.csv');
$csv = $drCsv->getCsv();
$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $periode);

if (DRevConfiguration::getInstance()->hasDenominationAuto()) {
  $drev->add('denomination_auto', DRevClient::DENOMINATION_BIO_TOTAL);
}
$drev->importCSVDouane($csv);
$drev->save();

$produits = array();
foreach ($csv as $line) {
    $key = $line[DouaneCsvFile::CSV_PRODUIT_INAO];
    if(DRevConfiguration::getInstance()->hasImportDRWithMentionsComplementaire()) {
        $key .= $line[DouaneCsvFile::CSV_PRODUIT_COMPLEMENT];
    }
    $produits[$key] = $key;
}

$t->comment("test sur ".$drev->_id);
$nb_produits_csv = count(array_keys($produits));
$t->is(count($drev->getProduits()), $nb_produits_csv, $nb_produits_csv." produits");
$drev->delete();

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $periode);
$csv[0][18] = "vin bio";
if (DRevConfiguration::getInstance()->hasDenominationAuto()) {
  $drev->add('denomination_auto', DRevClient::DENOMINATION_BIO_PARTIEL);
}
$drev->importCSVDouane($csv);
$drev->save();
$nb = 0;
foreach ($drev->declaration as $hash => $details) {
  $nb += count($details);
}
$t->is($nb, $nb_produits_csv, "bon nombre de produits si l'option automatique bio partiel activée et présence d'un bio en complement");
$firstdetail = $details->first;
$t->isnt($firstdetail->denomination_complementaire, DRevClient::DENOMINATION_BIO_LIBELLE_AUTO, "dénomination complémentaire si l'option automatique bio partiel");

$drev->delete();

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $periode);
if (DRevConfiguration::getInstance()->hasDenominationAuto()) {
  $drev->add('denomination_auto', DRevClient::DENOMINATION_BIO_TOTAL);
}
$drev->importCSVDouane($csv);
$drev->save();
$nb = 0;
foreach ($drev->declaration as $hash => $details) {
  $nb += count($details);
}
$t->is($nb, $nb_produits_csv, "bon nombre de produits si l'option automatique bio total activée");
$firstdetail = $details->first;
if (DRevConfiguration::getInstance()->hasDenominationAuto()) {
  $t->is($firstdetail->denomination_complementaire, DRevClient::DENOMINATION_BIO_LIBELLE_AUTO, "bonne dénomination de produit si l'option automatique bio total activée");
}else{
  $t->is($firstdetail->denomination_complementaire, "", "bonne dénomination de produit si l'option automatique bio total activée");
}

$drev->delete();

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $periode);
if (DRevConfiguration::getInstance()->hasDenominationAuto()) {
  $drev->add('denomination_auto', DRevClient::DENOMINATION_BIO_PARTIEL);
}
$csv[0][18] = null;
$drev->importCSVDouane($csv);
$drev->save();
$nb = 0;
foreach ($drev->declaration as $hash => $details) {
  $nb += count($details);
}
$nb_produits_csv_doublons = $nb_produits_csv;
if (DRevConfiguration::getInstance()->hasDenominationAuto() && $drev->hasDenominationAuto(DRevClient::DENOMINATION_BIO_PARTIEL)) {
    $nb_produits_csv_doublons *= 2;
}
$t->is($nb, $nb_produits_csv_doublons, "bon nombre de produits si l'option automatique bio partiel est activée");
$adetail = $details->first;
$t->isnt($adetail->denomination_complementaire, DRevClient::DENOMINATION_BIO_LIBELLE_AUTO, "dénomination complémentaire pour le produit non bio");
$adetail = $details->last;
if (DRevConfiguration::getInstance()->hasDenominationAuto()) {
  $t->is($adetail->denomination_complementaire, DRevClient::DENOMINATION_BIO_LIBELLE_AUTO, "dénomination complémentaire pour le produit bio");
}else{
  $t->is($adetail->denomination_complementaire, "", "dénomination complémentaire pour le produit bio");
}

$validation = new DRevValidation($drev);
$erreurs = $validation->getPointsByCodes('erreur');
$nb_bio = 0;
if (isset($erreurs['revendication_incomplete_volume']))
foreach($erreurs['revendication_incomplete_volume'] as $err) {
  if (preg_match('/ '.DRevClient::DENOMINATION_BIO_LIBELLE_AUTO.'/', $err->getInfo()) ) {
    $nb_bio++;
  }
}
$t->is($nb_bio, 0, "Pour les DRev avec bio partiel pas de point blocant sur les produits bios non remplis");
$t->is(isset($erreurs['declaration_volume_l15_dr']), false, "Pour les DRev avec bio partiel pas de point blocant sur le volume");
$t->is(isset($erreurs['revendication_superficie_dr']), false, "Pour les DRev avec bio partiel pas de point blocant sur la superficie");

$drev->validate();
$drev->save();
$nb = 0;
foreach ($drev->declaration as $hash => $details) {
  $nb += count($details);
}
$t->is($nb, $nb_produits_csv, "bon nombre de produits si l'option automatique bio partiel est activée et pas rempli les produits bio");

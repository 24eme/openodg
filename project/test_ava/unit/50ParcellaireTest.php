<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

$csvfile =  __DIR__."/../data/parcellaire-7523700100.csv";
$contextInstance = sfContext::getInstance();
$errors = array();
$t = new lime_test(4);

$csv = new Csv($csvfile);
$idu2produit = array();
foreach ($csv->getLignes() as $l) {
    $idu2produit[$l[ParcellaireScrappedCsvFile::CSV_FORMAT_IDU]] = $l[ParcellaireScrappedCsvFile::CSV_FORMAT_PRODUIT]. ' / '.$l[ParcellaireScrappedCsvFile::CSV_FORMAT_CEPAGE];
}

$viti = EtablissementClient::getInstance()->find('ETABLISSEMENT-7523700100');

$returncsv = ParcellaireClient::getInstance()->saveParcellaireCSV($viti, $csvfile, $errors, $contextInstance);
$t->is($returncsv, 1, "Le parcellaire a été créé à partir du fichier csv");

$parcellaire = ParcellaireClient::getInstance()->getLast($viti->identifiant);
$t->ok($parcellaire, "Le parcellaire est récupérable via getLast : ".$parcellaire->_id);

$t->is(count($parcellaire->getParcelles()), 360, "Le parcellaire contient le bon nombre de parcelles");
$t->ok(!$parcellaire->declaration->exist('certifications/DEFAUT/genres/DEFAUT/appellations/DEFAUT/mentions/DEFAUT/lieux/DEFAUT/couleurs/DEFAUT/cepages/DEFAUT'), "Toutes les parcelles sont reconnues");
if ($parcellaire->declaration->exist('certifications/DEFAUT/genres/DEFAUT/appellations/DEFAUT/mentions/DEFAUT/lieux/DEFAUT/couleurs/DEFAUT/cepages/DEFAUT')) {
    foreach($parcellaire->declaration->get('certifications/DEFAUT/genres/DEFAUT/appellations/DEFAUT/mentions/DEFAUT/lieux/DEFAUT/couleurs/DEFAUT/cepages/DEFAUT')->detail as $d) {
        $t->fail($idu2produit[$d->idu].' de la parcelle '.$d->idu.' aurait du être reconnue');
    }
}

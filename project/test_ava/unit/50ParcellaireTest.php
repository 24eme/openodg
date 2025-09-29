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

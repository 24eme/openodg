<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if (in_array($application, array('nantes', 'loire'))) {
    $t = new lime_test(1);
    $t->ok(true, "pas de parcellaire activé");
    return;
}

$t = new lime_test(6);
$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();
$campagne = date('Y');

foreach(ParcellaireIrrigableClient::getInstance()->getHistory($viti->identifiant, ParcellaireIrrigableClient::TYPE_COUCHDB, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $parcellaireIrrigable = ParcellaireIrrigableClient::getInstance()->find($k);
    $parcellaireIrrigable->delete(false);
}

$parcellaire = ParcellaireClient::getInstance()->getLast($viti->identifiant);

$parcellaireIrrigable = ParcellaireIrrigableClient::getInstance()->createDoc($viti->identifiant, $campagne);
$parcellaireIrrigable->save();

$t->is($parcellaireIrrigable->_id, 'PARCELLAIREIRRIGABLE-'.$viti->identifiant.'-'.$campagne, "L'id du doc est ".'PARCELLAIREIRRIGABLE-'.$viti->identifiant.'-'.$campagne);

$t->ok($parcellaireIrrigable->getParcellesFromReference(), "le parcellaire irrigué peut récupérer les parcelles du parcellaire");
$t->is(count($parcellaireIrrigable->getParcellesFromReference()), count($parcellaire->getParcelles()), "Le parcellaire à ".count($parcellaire->getParcelles())." parcelles");
$t->is(count($parcellaireIrrigable->declaration->getParcelles()), 0, "Le parcellaire irrigable n'a pas de parcelle");
$pids = array_keys($parcellaireIrrigable->getParcellaire()->declaration->getParcelles());
$parcellaireIrrigable->setParcellesFromParcellaire($pids);
$parcellaireIrrigable->save();

$t->is(count($parcellaireIrrigable->declaration->getParcellesByCommune()), 2, "Le parcellaire irrigable a 2 commune de parcelles");

$parcellaireIrrigable->validate();
$parcellaireIrrigable->save();

$t->is($parcellaireIrrigable->pieces[0]->libelle, "Identification des parcelles irrigables (Télédéclaration)", "La déclaration a bien généré un document (une pièce)");

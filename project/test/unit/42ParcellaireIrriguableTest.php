<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

sfContext::createInstance($configuration);

$t = new lime_test(5);
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

$t->is(count($parcellaireIrrigable->getParcellesFromLastParcellaire()->getParcelles()), count($parcellaire->getParcelles()), "Le parcellaire à ".count($parcellaire->getParcelles())." parcelles");
$t->is(count($parcellaireIrrigable->declaration->getParcelles()), 0, "Le parcellaire irrigable n'a pas de parcelle");

$parcellaireIrrigable->addParcellesFromParcellaire(array_keys($parcellaireIrrigable->getParcellaireCurrent()->getParcelles()));
$parcellaireIrrigable->save();

$t->is(count($parcellaireIrrigable->declaration->getParcellesByCommune()), count($parcellaire->getParcelles()), "Le parcellaire irrigable a 2 parcelles");

$parcellaireIrrigable->validate();
$parcellaireIrrigable->save();

$t->is($parcellaireIrrigable->pieces[0]->libelle, "Intention de parcelles irrigables ".$parcellaireIrrigable->campagne."-".($parcellaireIrrigable->campagne + 1)." (Télédéclaration)", "La déclaration a bien généré un document (une pièce)");

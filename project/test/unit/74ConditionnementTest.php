<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Test disabled");
    return;
}


$t = new lime_test(1);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des Conditioinnement précédents
foreach(ConditionnementClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $conditionnement = ConditionnementClient::getInstance()->find($k);
    $conditionnement->delete(false);
}

$campagne = (date('Y')-1)."";
//Début des tests
$t->comment("Création d'un Conditionnement");

$conditionnement = ConditionnementClient::getInstance()->createDoc($viti->identifiant, $campagne);
$conditionnement->storeDeclarant();
$conditionnement->save();

$lot = $conditionnement->addLot();
$lot->volume = 12;
$conditionnement->save();

$validation = new ConditionnementValidation($conditionnement);

$t->is(count($validation->getPointsByCode(ConditionnementValidation::TYPE_ERROR, "lot_produit_non_saisi")), 1, "Point bloquant:Aucun produit saisi lors de l'etape Lot");

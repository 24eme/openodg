<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Test disabled");
    return;
}


$t = new lime_test(2);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des Transactions précédents
foreach(TransactionClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $transaction = TransactionClient::getInstance()->find($k);
    $transaction->delete(false);
}

$campagne = (date('Y')-1)."";
//Début des tests
$t->comment("Création d'une Transaction");

$transaction = TransactionClient::getInstance()->createDoc($viti->identifiant, $campagne);

$transaction->storeDeclarant();
$transaction->save();

$produits = $transaction->getConfigProduits();

foreach ($produits as $key => $produit) {
  break;
}
$lot = $transaction->addLot();
$lot->volume = 12;
$lot = $transaction->addLot();
$lot->produit_hash = $produit->getHash();
$transaction->save();

$validation = new TransactionValidation($transaction);

$t->is(count($validation->getPointsByCode(TransactionValidation::TYPE_ERROR, "lot_produit_non_saisi")), 1, "Point bloquant:Aucun produit saisi lors de l'etape Lot");
$t->is(count($validation->getPointsByCode(TransactionValidation::TYPE_ERROR, "lot_volume_non_saisi")), 1, "Point bloquant:Aucun volume saisi lors de l'etape Lot");

<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Test disabled");
    return;
}


$t = new lime_test(13);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des Transactions précédents
foreach(TransactionClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $transaction = TransactionClient::getInstance()->find($k);
    $transaction->delete(false);
}

$year = date('Y');
if (date('m') < 8) {
    $year = $year - 1;
}
$campagne = sprintf("%04d-%04d", $year , $year + 1 );
$date = $year.'10-28';
//Début des tests
$t->comment("Création d'une Transaction");

$transaction = TransactionClient::getInstance()->createDoc($viti->identifiant, $date);

$transaction->storeDeclarant();
$transaction->save();

$t->is($transaction->type_archive, "Revendication", "Type d'archive Revendication");
$t->is($transaction->numero_archive, null, "Numéro d'archive nul");
$t->comment($transaction->_id);
$t->is($transaction->_id, 'TRANSACTION-'.$viti->identifiant.'-'.$year.'1028', "L'identifiant de la transaction est celui attendu");

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

$transaction->remove('lots');

$lot = $transaction->addLot();
$lot->volume = 12;
$lot->produit_hash = $produit->getHash();
$lot->numero_logement_operateur = "A";

$transaction->validate();
$transaction->save();

$t->ok($transaction->numero_archive, "Numéro d'archive défini");

$transaction->validateOdg();
$transaction->save();


$t->comment("Historique de mouvements");
$lot = $transaction->lots[0];

$t->is(count($transaction->lots->toArray(true, false)), 1, "Un lot");
$t->ok($lot->numero_dossier, "Numéro de dossier");
$t->ok($lot->numero_archive, "Numéro d'archive");
$t->is(count($lot->getMouvements()), 2, "2 mouvements pour le lot");
$t->ok($lot->getMouvement(Lot::STATUT_ENLEVE), 'Le lot est enlevé');
$t->ok($lot->getMouvement(Lot::STATUT_AFFECTABLE), 'Le lot est affectable');
$t->is($lot->getTypeProvenance(), null, "pas de provenance");


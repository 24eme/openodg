<?php

require_once(dirname(__FILE__).'/../bootstrap/common.php');

if ($application != 'igp13') {
    $t = new lime_test(1);
    $t->ok(true, "Test disabled");
    return;
}


$t = new lime_test(16);

$viti =  CompteTagsView::getInstance()->findOneCompteByTag('test', 'test_viti')->getEtablissement();

//Suppression des Transactions précédents
foreach(TransactionClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $transaction = TransactionClient::getInstance()->find($k);
    $transaction->delete(false);
}
foreach(ConditionnementClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $conditionnement = ConditionnementClient::getInstance()->find($k);
    $conditionnement->delete(false);
}
foreach(ChgtDenomClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $cd = ChgtDenomClient::getInstance()->find($k);
    $cd->delete(false);
}
foreach(DrevClient::getInstance()->getHistory($viti->identifiant, acCouchdbClient::HYDRATE_ON_DEMAND) as $k => $v) {
    $drev = DrevClient::getInstance()->find($k);
    $drev->delete(false);
}
foreach(ArchivageAllView::getInstance()->getDocsByTypeAndCampagne('Revendication', $campagne, 0, 99999, "%05d") as $r) {
    $doc = acCouchdbManager::getClient()->find($r->id);
    $doc->delete();
}

$year = date('Y');
if (date('m') < 8) {
    $year = $year - 1;
}
$campagne = sprintf("%04d-%04d", $year , $year + 1 );
$date = $year.'10-28';
//Début des tests

$drev = DRevClient::getInstance()->createDoc($viti->identifiant, $campagne);
$drev->validate();
$drev->validateOdg();
$drev->save();
$t->is($drev->numero_archive, '00001', "La DRev créée pour tester la mise en commun des numéros d'archive avec la Transaction");
$conditionnement = ConditionnementClient::getInstance()->createDoc($viti->identifiant, $campagne, $date);
$conditionnement->validate();
$conditionnement->validateOdg();
$conditionnement->save();
$t->is($conditionnement->numero_archive, '00002', "Le conditionnement créé pour tester la mise en commun des numéros d'archive avec la Transaction");


$t->comment("Création d'une Transaction");

$transaction = TransactionClient::getInstance()->createDoc($viti->identifiant, $campagne, $date);

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
$lot->specificite = null;
$lot = $transaction->addLot();
$lot->produit_hash = $produit->getHash();
$transaction->save();

$validation = new TransactionValidation($transaction);

$t->is(count($validation->getPointsByCode(TransactionValidation::TYPE_ERROR, "lot_incomplet")), 2, "Points bloquants sur chacun des lots");

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

$t->is(count($transaction->lots->toArray(true, false)), 1, "La transaction possède bien un lot");
$t->is($transaction->numero_archive, '00003', "Le numéro de dossier sur la transaction prend en compte la DREV et le conditionnement créé au début");
$t->is($lot->specificite, "", "La spécificité est vide");
$t->is($lot->numero_dossier, '00003', "Le numeor de dossier du lot reprend bien le numero d'archive de la transaction");
$t->is($lot->numero_archive, '00001', "Le numéro d'archive est bien le premier");
$t->is(count($lot->getMouvements()), 2, "2 mouvements pour le lot");
$t->ok($lot->getMouvement(Lot::STATUT_ENLEVE), 'Le lot est enlevé');
$t->ok($lot->getMouvement(Lot::STATUT_AFFECTABLE), 'Le lot est affectable');
$t->is($lot->getTypeProvenance(), null, "pas de provenance");

